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
            'user_id' => ['required', 'uuid', new UserExistsRule]
        ]);

        if ($validation->fails()) {
            return response($validation->errors(), 202);
        }

        $client = Client::create(request()->all());
        return $client::find($client->id);
    }
    public function getClient(Request $request)
    {
        $validation = Validator::make(['id' => $request->route('id')], [
            'id' => ['required', Rule::exists('clients', 'id')->whereNull('deleted_at')]
        ]);

        if ($validation->fails()) {
            return response($validation->errors(), 202);
        }

        $client = Client::with(['user', 'physicalAddress', 'postalAddress'])->find($request->route('id'));

        return response($client, 200);
    }
    public function listClients(Request $request)
    {
        $per_page = (int)$request['per_page'] ?? 10;
        $search_params = ['q', 'query_by', 'filter_by', 'sort_by', 'per_page', 'page', 'use_cache'];

        foreach ($search_params as $param) {
            if ($request->has($param)) {
                $request[$param] = urldecode($request[$param]);
            }
        }
        $clients = tap(
            Client::search()->options($request->only($search_params))->paginate($per_page),
            fn ($c) => $c->load(['physicalAddress', 'customFields'])
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
        $validation = Validator::make(['id' => $request->route('id'), ...$request->all()], [
            'id' => 'required|exists:clients,id',
            'first_name' => 'max:32|min:2',
            'last_name' => 'max:32|min:2',
            'email' => ['email', Rule::unique('clients', 'email')->ignore($request->route('id'))],
            'user_id' => ['uuid', new UserExistsRule]
        ]);

        if ($validation->fails()) {
            return response($validation->errors(), 202);
        }

        $client = Client::with(['user', 'physicalAddress', 'postalAddress'])->find($request->route('id'));

        $client->update($request->all());

        return response($client, 200);
    }
}
