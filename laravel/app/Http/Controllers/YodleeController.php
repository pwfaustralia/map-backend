<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class YodleeController extends Controller
{
    public function get($endpoint, $searchParams, $token)
    {
        $queryString = Arr::query($searchParams);
        try {
            $client = new Client();
            $yodlee_url = config('app.env') == 'production' ? config('app.yodlee_prod_url') : config('app.yodlee_sandbox_url');
            $response = $client->request('GET', $yodlee_url . $endpoint . '?' . $queryString, [
                'headers' => [
                    'Api-Version' => '1.1',
                    'Authorization' => "Bearer " . $token
                ]
            ]);
            return json_decode($response->getBody(), true);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            return json_decode($response->getBody(), true);
        }
    }
}
