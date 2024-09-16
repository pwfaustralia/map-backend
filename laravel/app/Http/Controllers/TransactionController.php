<?php

namespace App\Http\Controllers;

use App\Jobs\InitiateImportTransactions;
use App\Models\Client;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;

class TransactionController extends Controller
{
    public function importAccountTransactions(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'yodlee_username' => 'required'
        ]);
        if ($validation->fails()) {
            return response($validation->errors(), 202);
        }
        try {
            $client = Client::find($request['client_id']);
            $client->transactions()->forceDelete();
            $client->accounts()->forceDelete();
            $client->yodlee_status = "IMPORTING";
            $client->save();
            $job = (new InitiateImportTransactions($request['yodlee_username'], $request['client_id'], []))->onQueue('initiate-import-queue', []);
            Bus::dispatch($job);
            return response()->json($job->getResponse(), 200);
        } catch (Exception $e) {
            return  [
                "success" => false,
                "message" => $e->getMessage()
            ];
        }
    }
}
