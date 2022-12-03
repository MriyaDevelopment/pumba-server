<?php

namespace App\Http\Controllers;

use App\Messages\Messages;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $input = $request->all();

        $rules = array(
            'emailOrName' => 'required|string|max:255',
            'password' => 'required|string'
        );

        $validator = Validator::make($input, $rules);

        if ($validator->fails()) {
            return $this->sendError(Messages::allFieldsError);
        }

        $emailOrName = $input['emailOrName'];
        $password = $input['password'];

        $user = User::where('email', $emailOrName)->orWhere('name', $emailOrName)->first();

        if (!$user) {
            return $this->sendError(Messages::userError);
        }

        if (!Hash::check($password, $user->password)) {
            return $this->sendError(Messages::userPasswordError);
        }

        $api_token = $user['api_token'];

        return $this->sendResponse($api_token, 'api_token');
    }

    public function register(Request $request): JsonResponse {

        $input = $request->all();

        $rules = array(
            'email' => 'required|string|email|max:255|unique:users',
            'name' => 'required|string|unique:users',
            'password' => 'required|string'
        );

        $messages = array(
            'email.required|string|email|max:255|unique:users' => Messages::userRegisterEmailValidator,
            'name.required|string|unique:users' => Messages::userRegisterNameValidator,
        );

        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $email = $input['email'];
        $name = $input['name'];
        $password = Hash::make($input['password']);
        $api_token = Str::random(80);

        User::forceCreate([
            'email' => $email,
            'name' => $name,
            'password' => $password,
            'api_token' => $api_token,
        ]);

        return $this->sendSuccess(Messages::userRegisterSuccess);
    }
}
