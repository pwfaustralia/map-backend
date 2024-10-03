<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class YodleeController extends Controller
{
    public function get($endpoint, $searchParams, $token)
    {
        $queryString = Arr::query($searchParams);
        try {
            $client = new HttpClient();
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

    public function getCachedYodleeAccessToken($username)
    {
        $cache_key = 'yodlee_' . $username;
        if (!Cache::has($cache_key)) {
            $initialToken = app(YodleeController::class)->getYodleeAccessToken($username);
            if (!isset($initialToken->token)) {
                return [true, null];
            }
            $token_cache[$cache_key] = $initialToken->token->accessToken;
            cache($token_cache, now()->addMinutes(30));
            return [false, $initialToken->token->accessToken];
        }
        return [false, cache($cache_key)];
    }

    public function getYodleeAccessTokens(User $user, $return_as_array = false)
    {
        $yodlee_usernames = array_map(fn($c) => $c->yodlee_username, array_filter($user->clients->all(), fn($c) => $c->yodlee_username != ''));
        $yodlee_usernames = array_unique($yodlee_usernames);
        $yodlee_tokens = array();
        $error = null;

        foreach ($yodlee_usernames as $yodlee_username) {
            try {
                $client = new \GuzzleHttp\Client();
                $yodlee_url = config('app.env') == 'production' ? config('app.yodlee_prod_url') : config('app.yodlee_sandbox_url');
                $response = $client->request('POST', $yodlee_url . '/auth/token', [
                    'headers' => [
                        'Api-Version' => '1.1',
                        'loginName' => $yodlee_username,
                        'Content-Type' => 'application/x-www-form-urlencoded'
                    ],
                    'form_params' => [
                        'clientId' => config('app.yodlee_client_id'),
                        'secret' => config('app.yodlee_client_secret')
                    ]
                ]);
                $resp = json_decode($response->getBody());
                $resp->username = $yodlee_username;
                array_push($yodlee_tokens, $resp);
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                $response = $e->getResponse();
                $error = json_decode($response->getBody());
            }
        }

        if (!$return_as_array) {
            $yodlee_tokens = array_map(fn($tok) => $tok->username . '=' . $tok->token->accessToken, $yodlee_tokens);
            $yodlee_tokens = implode(';', $yodlee_tokens);
        }

        return [
            "tokens" => $yodlee_tokens,
            "error" => $error
        ];
    }

    public function getUserYodleeAccessTokenWithHeader(Request $request)
    {
        $validation = Validator::make(['id' => $request->route('id')], [
            'id' => 'required|exists:clients,id'
        ]);
        if ($validation->fails()) {
            return response($validation->errors(), 202);
        }
        $client = Client::find($request->route('id'));
        $yodlee_acess_token = $this->getYodleeAccessToken($request['username'] ?? $client->yodlee_username);
        return response()->json($yodlee_acess_token, 200);
    }

    public function getYodleeAccessToken($yodlee_username)
    {
        $error = null;
        $token = [];

        try {
            $client = new \GuzzleHttp\Client();
            $yodlee_url = config('app.env') == 'production' ? config('app.yodlee_prod_url') : config('app.yodlee_sandbox_url');
            $response = $client->request('POST', $yodlee_url . '/auth/token', [
                'headers' => [
                    'Api-Version' => '1.1',
                    'loginName' => $yodlee_username,
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'form_params' => [
                    'clientId' => config('app.yodlee_client_id'),
                    'secret' => config('app.yodlee_client_secret')
                ]
            ]);
            $resp = json_decode($response->getBody());
            $resp->username = $yodlee_username;
            $token = $resp;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $error = json_decode($response->getBody());
            $token['error'] = $error;
        }

        return $token;
    }

    public function getYodleeStatus(Request $request)
    {
        $validation = Validator::make(['id' => $request->route('id')], [
            'id' => 'required|exists:clients,id'
        ]);
        if ($validation->fails()) {
            return response($validation->errors(), 202);
        }
        $client = Client::where("id", $request->route('id'))->first(['yodlee_status']);

        return response()->json($client);
    }
}
