<?php

namespace App\Jobs;

use App\Http\Controllers\ClientController;
use App\Http\Controllers\YodleeController;
use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Batch;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Throwable;


class InitiateImportTransactions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filters;
    protected $token;
    protected $username;
    protected $clientId;
    protected $response;
    protected $accounts;
    protected $transactions;

    public function __construct($username, $clientId, $filters)
    {
        $cache_key = 'yodlee_' . $username;
        if (!Cache::has($cache_key)) {
            $initialToken = app(ClientController::class)->getYodleeAccessToken($username);
            if (!isset($initialToken->token)) {
                $this->response = [
                    "success" => false,
                    "message" => "Can't get yodlee access token for " . $username
                ];
                return;
            }
            $token_cache[$cache_key] = $initialToken->token->accessToken;
            $this->token = $initialToken->token->accessToken;
            cache($token_cache, now()->addMinutes(30));
        } else {
            $this->token = cache($cache_key);
        }
        $this->filters = $filters;
        $this->clientId = $clientId;
        $this->username = $username;
        $count = app(YodleeController::class)->get("/transactions/count", $this->filters, $this->token);
        $this->transactions = app(YodleeController::class)->get("/transactions", $this->filters, $this->token);
        $this->accounts = app(YodleeController::class)->get("/accounts", [], $this->token);
        $this->response = [
            "success" => true,
            "details" => [
                "client_id" => $this->clientId,
                "username" => $this->username,
                "filters" => $this->filters,
                "totalTransactions" => isset($count['transaction']) ? $count['transaction']['TOTAL']['count'] : 0,
                "totalAccounts" => count($this->accounts['account']) ?? 0
            ]
        ];
    }

    public function failed()
    {

        $client = Client::find($this->clientId);
        $client->yodlee_status = "IMPORT_FAILED";
        $client->save();
    }

    public function handle()
    {

        $transactionBatch = [];
        $transactionBatchId = Str::uuid();
        $accountId = '';
        $client = Client::find($this->clientId);
        $isPrimarySet = false;

        foreach ($this->transactions['transaction'] as &$data) {
            $accountId = $data['accountId'];
            $data['transaction_id'] = $data['id'];
            $data['createdDate'] = Carbon::parse($data['createdDate']);
            $data['lastUpdated'] = Carbon::parse($data['lastUpdated']);
            $data['amount'] = $data['amount']['amount'];
            $data['description'] = $data['description']['original'];
            $data['runningBalance'] = $data['runningBalance']['amount'];
            $data['batch_id'] = $transactionBatchId;
            array_push($transactionBatch, new ImportTransaction(camelToSnakeCaseArray($data)));
        }
        Bus::batch($transactionBatch)
            ->before(function (Batch $batch) {
                // The batch has been created but no jobs have been added...
            })->progress(function (Batch $batch) {
                // A single job has completed successfully...
            })->then(function (Batch $batch) {
                // All jobs completed successfully...
            })->catch(function (Batch $batch, Throwable $e) use ($client) {
                $client->yodlee_status = "IMPORT_FAILED";
                $client->save();
            })->finally(function (Batch $batch) use ($client) {
                $client->yodlee_status = "IMPORT_SUCCESS";
                $client->save();
            })
            ->name("Import Transactions [Account:$accountId | Username: $this->username]")->onQueue('import-queue')->dispatch();

        $accountBatch = [];
        $accountBatchId = Str::uuid();
        foreach ($this->accounts['account'] as &$data) {
            if (!$isPrimarySet && $data['CONTAINER'] === "loan") {
                $isPrimarySet = true;
                $data['is_primary'] = $isPrimarySet;
            }
            $data['account_id'] = $data['id'];
            $data['createdDate'] = Carbon::parse($data['createdDate']);
            $data['lastUpdated'] = Carbon::parse($data['lastUpdated']);
            $data['client_id'] = $this->clientId;
            $data['batch_id'] = $accountBatchId;
            array_push($accountBatch, new ImportAccount(camelToSnakeCaseArray($data)));
        }
        Bus::batch($accountBatch)
            ->before(function (Batch $batch) {
                // The batch has been created but no jobs have been added...
            })->progress(function (Batch $batch) {
                // A single job has completed successfully...
            })->then(function (Batch $batch) {
                // All jobs completed successfully...
            })->catch(function (Batch $batch, Throwable $e) use ($client) {
                $client->yodlee_status = "IMPORT_FAILED";
                $client->save();
            })->finally(function (Batch $batch) {
                // The batch has finished executing...
            })
            ->name("Import Accounts [Client:$this->clientId | Username: $this->username]")->onQueue('import-queue')->dispatch();
    }

    public function getResponse()
    {
        return $this->response;
    }
}
