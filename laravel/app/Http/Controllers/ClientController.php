<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Client;
use App\Models\LoanBalance;
use App\Rules\UserExistsRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function createClient(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'first_name' => 'required|max:32|min:2',
            'last_name' => 'required|max:32|min:2',
            'email' => 'required|email|unique:clients,email',
            'yodlee_username' => 'required',
            'user_id' => ['required', 'uuid', new UserExistsRule]
        ]);
        if ($validation->fails()) {
            return response($validation->errors(), 202);
        }

        $client = Client::create(request()->all());

        return response()->json($client);
    }

    public function getClient(Request $request)
    {
        $validation = Validator::make(['id' => $request->route('id')], [
            'id' => ['required', Rule::exists('clients', 'id')->whereNull('deleted_at')]
        ]);

        if ($validation->fails()) {
            return response($validation->errors(), 202);
        }

        $client = Client::with(['user', 'primaryAccount'])->withCount(['accounts', 'transactions'])->find($request->route('id'));

        return response($client, 200);
    }
    public function listClients(Request $request)
    {
        $per_page = (int)$request['per_page'] ?? 10;
        $search_params = ['q', 'query_by', 'filter_by', 'sort_by', 'per_page', 'page', 'use_cache', 'facet_query', 'facet_by', 'group_by'];

        foreach ($search_params as $param) {
            if ($request->has($param)) {
                $request[$param] = urldecode($request[$param]);
            }
        }
        $clients = tap(
            Client::search()->options($request->only($search_params))->paginate($per_page),
            fn($c) => $c->load(['customFields'])
        );

        return response($clients, 200);
    }
    public function deleteClient(Request $request)
    {
        $validation = Validator::make(['id' => $request->route('id')], [
            'id' => 'required|exists:clients,id'
        ]);

        if ($validation->fails()) {
            return response($validation->errors(), 202);
        }

        $client_to_be_deleted = Client::find($request->route('id'));
        $client_to_be_deleted->delete();

        if ($client_to_be_deleted->trashed()) {
            return response(['deleted' => true], 200);
        } else {
            return response(['deleted' => false], 200);
        }
    }
    public function updateClient(Request $request)
    {
        $id = $request['client_id'] ?? $request->route('id');
        $validation = Validator::make(['id' => $id, ...$request->all()], [
            'id' => 'required|exists:clients,id',
            'first_name' => 'max:32|min:2',
            'last_name' => 'max:32|min:2',
            'email' => ['email', Rule::unique('clients', 'email')->ignore($id)],
            'user_id' => ['uuid', new UserExistsRule]
        ]);

        if ($validation->fails()) {
            return response($validation->errors(), 202);
        }

        $client = Client::with(['user'])->find($id);

        $client->update($request->all());

        return response($client, 200);
    }

    public function getLoanAccounts(Request $request)
    {
        $validation = Validator::make(['id' => $request->route('id')], [
            'id' => 'required|exists:clients,id'
        ]);
        if ($validation->fails()) {
            return response($validation->errors(), 202);
        }
        $client = Client::where("id", $request->route('id'))->first();
        if ($request['primary'] == true) {
            return response()->json($client->primaryAccount, 200);
        }
        return response()->json($client->accounts, 200);
    }

    public function setPrimaryLoanAccount(Request $request)
    {
        $validation = Validator::make(['id' => $request->route('id'), ...$request->all()], [
            'id' => 'required|exists:clients,id',
            'account_id' => 'required|exists:accounts,account_id'
        ]);
        if ($validation->fails()) {
            return response($validation->errors(), 202);
        }

        $client = Client::where("id", $request->route('id'))->first();
        $client->accounts()->update(["is_primary" => false]);
        $primaryAccount = Account::whereAccountId($request['account_id'])->first();
        $updateResult = $primaryAccount->update(["is_primary" => true]);

        if (!$updateResult) {
            return response()->json([
                "success" => false,
                "message" => "Failed to set Primary Loan Account"
            ], 200);
        }

        if ($primaryAccount->container === 'loan') {
            // delete existing account calculation
            LoanBalance::whereLoanAccountId($primaryAccount->account_id)->forceDelete();
            // get yodlee access token
            $getAccessToken = app(YodleeController::class)->getCachedYodleeAccessToken($client->yodlee_username);
            if ($getAccessToken[0]) {
                return response()->json([
                    "success" => false,
                    "message" => "Primary Loan Account has been set. However the system could not calculate the loan balance due to missing data"
                ], 200);
            }
            // get average monthly expenses
            $transactionSummary = app(YodleeController::class)
                ->get(
                    "/derived/transactionSummary",
                    [
                        "fromDate" => date("Y-m-d", strtotime("-12 months")),
                        "toDate" => date('Y-m-d'),
                        "groupBy" => "CATEGORY",
                        "categoryType" => "INCOME,EXPENSE"
                    ],
                    $getAccessToken[1]
                );
            $loanDepositSummary = app(YodleeController::class)
                ->get(
                    "/derived/transactionSummary",
                    [
                        "fromDate" => date("Y-m-d", strtotime("-12 months")),
                        "toDate" => date('Y-m-d'),
                        "groupBy" => "CATEGORY",
                        "categoryType" => "TRANSFER",
                        "accountId" => $primaryAccount->account_id
                    ],
                    $getAccessToken[1]
                );
            if (isset($transactionSummary['transactionSummary'])) {
                // set average income and expenses
                $averageLoanDeposit = 0;
                $averageCombinedIncome = 0;
                $averageMonthlyExpenses = 0;
                foreach ($transactionSummary['transactionSummary'] as $summary) {
                    if ($summary['categoryType'] === 'INCOME') {
                        $averageCombinedIncome += $summary['creditTotal']['amount'] / 12;
                    } else if ($summary['categoryType'] === 'EXPENSE') {
                        $averageMonthlyExpenses += $summary['debitTotal']['amount'] / 12;
                    }
                }
                // set average loan deposit
                if (isset($loanDepositSummary['transactionSummary'])) {
                    $averageLoanDeposit = $loanDepositSummary['transactionSummary'][0]['debitTotal']['amount'] / 12;
                } else {
                    $averageLoanDeposit = $averageCombinedIncome - $averageMonthlyExpenses;
                }
                // calculate loan balance scenarios
                $loanValue = $primaryAccount->original_loan_amount->getMinorAmount()->toInt() / 100;
                $batch = LoanBalanceController::generateNormalLoanBalanceScenario($loanValue, 0.0569, 24, $primaryAccount->account_id, $averageLoanDeposit);
                $job1 = Bus::batch($batch)->name("Generate Normal Loan Balance Scenario")->onQueue("generate-loan-balance-scenario")->dispatch();

                $batch = LoanBalanceController::generateOffsetLoanBalanceScenario($loanValue, 24, $primaryAccount->account_id, $averageCombinedIncome, $averageMonthlyExpenses);
                $job2 = Bus::batch($batch)->name("Generate Offset Loan Balance Scenario")->onQueue("generate-loan-balance-scenario")->dispatch();

                return response()->json([
                    "success" => true,
                    "message" => "Primary Loan Account has been set and loan balance is being calculated in the background.",
                    "jobs" => ["job1" => $job1, "job2" => $job2]
                ], 200);
            }
        }

        return response()->json([
            "success" => false,
            "message" => "Primary Loan Account has been set. However the system could not calculate the loan balance due to missing data."
        ], 200);
    }
}
