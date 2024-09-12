<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Models\Transaction; // Assuming you have a Transaction model
use Illuminate\Support\Facades\Log;

class ImportTransactions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->queue = 'transaction';
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function handle()
    {
        // Yodlee API endpoint and access token retrieval
        $baseUrl = 'https://sandbox.api.yodlee.com.au/ysl';
        $endpoint = '/transactions';
        $accessToken = $this->getYodleeAccessToken();

        // Fetch transactions
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
            'Api-Version' => '1.1'
        ])->get("https://sandbox.api.yodlee.com.au/ysl/transactions");
        var_dump($response->json());
        // if ($response->ok()) {
        //     $transactions = $response->json()['transaction'];

        //     // Save transactions into your local database
        //     foreach ($transactions as $transaction) {
        //         Transaction::updateOrCreate(
        //             ['transaction_id' => $transaction['id']],  // Assuming Yodlee's transaction ID is unique
        //             [
        //                 'account_id' => $transaction['accountId'],
        //                 'amount' => $transaction['amount']['amount'],
        //                 'currency' => $transaction['amount']['currency'],
        //                 'description' => $transaction['description']['original'],
        //                 'date' => $transaction['date'],
        //                 'category' => $transaction['category'],
        //             ]
        //         );
        //     }

        //     Log::info('Yodlee transactions successfully imported.');
        // } else {
        //     Log::error('Failed to fetch Yodlee transactions', ['error' => $response->json()]);
        // }
    }

    /**
     * Retrieve Yodlee Access Token
     */
    private function getYodleeAccessToken()
    {
        // Replace this with the actual logic to retrieve the access token
        // You can store the token in a config or database for reusability
        return 'AdxtpHtknd6VHrvpH7ySP1OHdYML';
    }
}
