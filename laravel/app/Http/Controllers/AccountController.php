<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
    public function assignToClient(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'account_id' => 'required|exists:accounts,account_id',
            'client_id' => 'required|exists:clients,id'
        ]);
        if ($validation->fails()) {
            return response($validation->errors(), 202);
        }
        $account = Account::where('account_id', $request['account_id'])->first();
        $account->client_id = $request['client_id'];

        return $account->save();
    }
}
