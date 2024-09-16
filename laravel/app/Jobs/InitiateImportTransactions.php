<?php

namespace App\Jobs;

use App\Http\Controllers\ClientController;
use App\Http\Controllers\YodleeController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Models\Transaction; // Assuming you have a Transaction model
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class InitiateImportTransactions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filters;
    protected $token;
    protected $username;

    public function __construct($username, $filters)
    {
        $cache_key = 'yodlee_' . $username;
        if (!Cache::has($cache_key)) {
            $initialToken = app(ClientController::class)->getYodleeAccessToken($username);
            if (!isset($initialToken->token)) {
                echo "can't get yodlee access token for " . $username;
                return;
            }
            $token_cache[$cache_key] = $initialToken->token->accessToken;
            $this->token = $initialToken->token->accessToken;
            echo "token set for $username: " . $initialToken->token->accessToken;
            cache($token_cache, now()->addMinutes(30));
        } else {
            $this->token = cache($cache_key);
        }

        $this->username = $username;
        $this->filters = $filters;
        var_dump($this->token);
        var_dump($this->username);
    }

    public function handle()
    {
        $transactions = app(YodleeController::class)->get("/transactions", $this->filters, $this->token);
        $batch = [];
        $accountId = '';
        foreach ($transactions['transaction'] as &$data) {
            $accountId = $data['accountId'];
            $data['transaction_id'] = $data['id'];
            $data['createdDate'] = Carbon::parse($data['createdDate']);
            $data['lastUpdated'] = Carbon::parse($data['lastUpdated']);
            $data['amount'] = $data['amount']['amount'];
            $data['description'] = $data['description']['original'];
            $data['runningBalance'] = $data['runningBalance']['amount'];
            array_push($batch, new ImportTransaction(camelToSnakeCaseArray($data)));
        }
        Bus::batch($batch)->name("Import Transactions [Account:$accountId | Username: $this->username]")->onQueue('import-queue')->dispatch();
    }
}
