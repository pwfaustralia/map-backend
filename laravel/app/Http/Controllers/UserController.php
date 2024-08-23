<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'name' => 'required|max:32|min:2',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()]
        ]);
        if ($validation->fails()) {
            return response($validation->errors(), 202);
        }

        $user_data = $request->all();
        $user_data['password'] = bcrypt($user_data['password']);
        $user = User::create($user_data);


        if ($request['with_client'] === true) {
            $cookie = $request->cookie('laravel_access_token');
            $header = $request->header('Authorization');
            $access_token = $cookie ? 'Bearer ' . $cookie : $header;
            $response = Http::withHeaders([
                'Authorization' => $access_token
            ])->post(
                'http://localhost/api/clients',
                [
                    ...$request->all(
                        ['first_name', 'last_name', 'middle_name', 'preferred_name', 'yodlee_username', 'email'],
                    ),
                    'user_id' => $user->id
                ]
            );
            $create_client_resp = $response->json();
            // $user->forceDelete();
            return response()->json($create_client_resp);
            if (!isset($create_client_resp['id'])) {
                // $user->forceDelete();
                return response()->json($response->body());
            }
        }
        // $user->forceDelete();
        // event(new Registered($user));

        return response()->json($user, 200);
    }

    public function login()
    {
        $credentials = request(['email', 'password']);

        if (!Auth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid email/password.'], 401);
        }

        return $this->respondWithToken($credentials['email']);
    }

    protected function respondWithToken($email)
    {
        $user = User::with([
            'userRole.rolePermissions' => function ($query) {
                $query->select('user_role_id', 'scope_name');
            },
            'clients'
        ])->where('email', '=', $email)->first();
        $scopes = [];
        foreach ($user->userRole->rolePermissions as $permission) {
            array_push($scopes, $permission->scope_name);
        }
        unset($user->userRole->rolePermissions);
        $user->userRole->rolePermissions = $scopes;
        $token_obj = $user->createToken("$user->name Access Token", $scopes);
        $access_token = $token_obj->accessToken;

        if ($user->userRole->role_name == 'Super Admin') {
        }
        $default_page = "";
        switch ($user->userRole->role_name) {
            case 'Super Admin':
                $default_page = '/clients';
                break;
            case 'Admin':
                $default_page = '/clients';
                break;
            case 'Staff':
                $default_page = '/clients';
                break;
            case 'Client':
                $default_page = '/dashboard';
                break;
        }


        $user['default_page'] = $default_page;
        $yodlee_usernames = array_map(fn($c) => $c->yodlee_username, array_filter($user->clients->all(), fn($c) => $c->yodlee_username != ''));
        $yodlee_usernames = array_unique($yodlee_usernames);
        $yodlee_tokens = array();

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
                $user['yodlee_error'] = json_decode($response->getBody());
            }
        }

        $yodlee_tokens = array_map(fn($tok) => $tok->username . '=' . $tok->token->accessToken, $yodlee_tokens);
        $yodlee_tokens = implode(';', $yodlee_tokens);

        $expiration = $token_obj->token->expires_at->diffInSeconds(Carbon::now());

        return response()->json($user, 200)->withCookie(cookie(
            'laravel_access_token',
            $access_token,
            $expiration
        ))->withHeaders([
            'X-Yodlee-AccessToken' => $yodlee_tokens,
        ]);
    }

    public function getUser(Request $request)
    {
        $validation = Validator::make(['id' => $request->route('id')], [
            'id' => ['required', Rule::exists('users', 'id')->whereNull('deleted_at')]
        ]);

        if ($validation->fails()) {
            return response($validation->errors(), 202);
        }

        $user = User::with(['userRole', 'clients'])->find($request->route('id'));

        return response($user, 200);
    }

    public function listUsers()
    {
        $users = User::with(['userRole'])->paginate(10);

        return response($users, 200);
    }

    public function deleteUser(Request $request)
    {
        $validation = Validator::make(['id' => $request->route('id')], [
            'id' => 'required|exists:users,id'
        ]);

        if ($validation->fails()) {
            return response($validation->errors(), 202);
        }

        $user_to_be_deleted = User::find($request->route('id'));
        $user_to_be_deleted->delete();

        if ($user_to_be_deleted->trashed()) {
            return response(['deleted' => true], 200);
        } else {
            return response(['deleted' => false], 200);
        }
    }

    public function updateUser(Request $request)
    {
        $validation = Validator::make(['id' => $request->route('id'), ...$request->all()], [
            'id' => 'required|exists:users,id',
            'name' => 'max:32|min:2',
            'email' => ['email', Rule::unique('users', 'email')->ignore($request->route('id'))],
            'user_role_id' => 'exists|user_roles,id'
        ]);

        if ($validation->fails()) {
            return response($validation->errors(), 202);
        }

        $user = User::with(['userRole', 'clients'])->find($request->route('id'));

        $user->update($request->all());

        return response($user, 200);
    }

    public function me()
    {
        return $this->respondWithToken(Auth::user()->email);
    }

    public function access_checkup()
    {
        if (Auth::user() === null) return response()->json(['success' => false], 401)->withCookie(cookie(
            'laravel_access_token',
            null
        ));;
        return response()->json(['success' => true], 200);
    }

    public function logout()
    {
        Auth::user()->tokens->each(function ($token) {
            $token->delete();
        });

        return response()->json(["success" => true], 200)->withCookie(cookie(
            'laravel_access_token',
            null
        ));
    }
}
