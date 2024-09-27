<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateLoanBalanceScenario;
use App\Models\LoanBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LoanBalanceController extends Controller
{
    public static $creditCardLimit = 1500000;

    public function listLoanBalances(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'scenario' => 'required|in:normal,offset',
            'loan_account_id' => 'required|exists:accounts,account_id'
        ]);
        if ($validation->fails()) {
            return response($validation->errors(), 202);
        }

        $loanBalances = LoanBalance::where($request->only(["scenario", "loan_account_id"]));

        if ($request['by'] === 'year') {
            $loanBalances = DB::select(
                '
                SELECT
                scenario,
                (CASE
                    WHEN month = 0
                    THEN 1
                    ELSE CEIL(month/12)
                END) as year,
                ROUND(CAST(balance/100 as float), 2) as balance,
                ROUND(CAST(deposit/100 as float), 2) as deposit,
                ROUND(CAST(offset_amount/100 as float), 2) as offset_amount,
                ROUND(CAST(credit_card_amount/100 as float), 2) as credit_card_amount,
                ROUND(CAST(offset_balance/100 as float), 2) as offset_balance
                FROM loan_balances
                WHERE scenario = ?
                AND loan_account_id = ?
                AND month != 1
                AND (month = 0 or MOD(month - 1, 12) = 0)',
                [$request['scenario'], $request['loan_account_id']]
            );
        } else {
            $loanBalances = DB::select(
                '
                SELECT
                scenario,
                month,
                ROUND(CAST(balance/100 as float), 2) as balance,
                ROUND(CAST(deposit/100 as float), 2) as deposit,
                ROUND(CAST(offset_amount/100 as float), 2) as offset_amount,
                ROUND(CAST(credit_card_amount/100 as float), 2) as credit_card_amount,
                ROUND(CAST(offset_balance/100 as float), 2) as offset_balance
                FROM loan_balances
                WHERE scenario = ?
                AND loan_account_id = ?',
                [$request['scenario'], $request['loan_account_id']]
            );
        }

        return response()->json($loanBalances, 200);
    }

    public static function calculateLoanBalance($loanValue, $interestRate, $months, $monthlyPayment, $currentMonth)
    {
        $compoundInterest = pow((1 + $interestRate / $months), $currentMonth);
        $loanBalance = $loanValue * $compoundInterest -
            $monthlyPayment * (($compoundInterest - 1) / ($interestRate / $months));
        return $loanBalance;
    }

    public static function generateNormalLoanBalanceScenario($loanValue, $interestRateInDecimal, $term, $accountId, $monthsInYear = 12, $currency = 'AUD')
    {
        $monthlyPayment = PMT($interestRateInDecimal / $monthsInYear, $term * $monthsInYear, -$loanValue);

        $loanBalanceList = array_map(function ($data) use ($loanValue, $interestRateInDecimal, $monthsInYear, $monthlyPayment, $currency, $accountId) {
            static $index = -1;
            $index++;
            $deposit = 7785 * ($index - 1);

            return new GenerateLoanBalanceScenario([
                "month" => $index,
                "balance" => round(LoanBalanceController::calculateLoanBalance($loanValue, $interestRateInDecimal, $monthsInYear, $monthlyPayment, $index), 2),
                "deposit" => $deposit,
                "currency" => $currency,
                "loan_account_id" => $accountId,
                "scenario" => 'normal'
            ]);
        }, array_fill(0, $term * $monthsInYear, null));

        return $loanBalanceList;
    }

    public static function generateOffsetLoanBalanceScenario($loanValue, $term, $accountId, $combinedMonthlyIncome, $avgMonthlyExpenses, $monthsInYear = 12, $currency = 'AUD')
    {
        $loanBalanceList = array_map(function ($data) use ($loanValue, $combinedMonthlyIncome, $avgMonthlyExpenses, $currency, $accountId) {
            static $index = -1;
            static $offsetBalance = 0;
            $index++;

            $creditCardAmount = $avgMonthlyExpenses;
            if ($index === 0) {
                $offsetAmount = $combinedMonthlyIncome;
                $offsetBalance = $offsetAmount - $creditCardAmount;
                $balance = $loanValue;
            } else {
                $offsetAmount = $combinedMonthlyIncome + $offsetBalance;
                $offsetBalance = $offsetAmount - $creditCardAmount;
                $balance = $loanValue - $offsetBalance;
            }
            $isFullyPaid = $loanValue - $offsetBalance < 0;
            if ($isFullyPaid) {
                $deposit = abs($balance);
                $balance = 0;
            } else {
                $deposit = 0;
            }

            return new GenerateLoanBalanceScenario([
                "month" => $index,
                "balance" => $balance,
                "deposit" => $deposit,
                "currency" => $currency,
                "loan_account_id" => $accountId,
                "offset_amount" => $offsetAmount,
                "credit_card_amount" => $creditCardAmount,
                "offset_balance" => $offsetBalance,
                "scenario" => 'offset'
            ]);
        }, array_fill(0, $term * $monthsInYear, null));

        return $loanBalanceList;
    }
}
