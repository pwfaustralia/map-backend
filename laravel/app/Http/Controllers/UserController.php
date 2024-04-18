<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        event(new Registered($user));

        return response()->json($user, 200);
    }
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (!Auth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($credentials['email']);
    }
    protected function respondWithToken($email)
    {
        $user = User::with('userRole.rolePermissions')->where('email', '=', $email)->first();
        $scopes = [];
        foreach ($user->userRole->rolePermissions as $permission) {
            array_push($scopes, $permission->scope_name);
        }
        $access_token = $user->createToken("$user->name Access Token", $scopes)->accessToken;

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

        return response()->json($user, 200)->cookie(
            'accessToken',
            $access_token,
            1440
        );
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
}
