<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Rules\UserExistsRule;
use Illuminate\Http\Request;
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

        $client = Client::with(['user'])->withCount('accounts')->find($request->route('id'));

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
}
