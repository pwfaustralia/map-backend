<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::get('/', function () {
    return view('welcome');
});
// Route::get('/redirect', function (Request $request) {
//     $request->session()->put('state', $state = Str::random(40));

//     $query = http_build_query([
//         'client_id' => 'client-id',
//         'redirect_uri' => 'http://third-party-app.com/callback',
//         'response_type' => 'code',
//         'scope' => '',
//         'state' => $state,
//         // 'prompt' => '', // "none", "consent", or "login"
//     ]);

//     return redirect('http://passport-app.test/oauth/authorize?' . $query);
// });
