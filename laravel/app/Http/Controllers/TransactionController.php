<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function importAccountTransactions(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'account_id' => 'required|exists:accounts,account_id'
        ]);
        if ($validation->fails()) {
            return response($validation->errors(), 202);
        }

        $account = Account::find($request['account_id'])->get();
        $client = $account->client();
        $validation = Validator::make($client, [
            'id' => 'required|exists:clients,id'
        ]);
        if ($validation->fails()) {
            return response($validation->errors(), 202);
        }

        return camelToSnakeCaseArray($account->toArray());
    }
}
