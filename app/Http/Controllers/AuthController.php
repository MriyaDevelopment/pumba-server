<?php

namespace App\Http\Controllers;

use App\Http\Controllers\API\Controller;
use App\Messages\Messages;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     * path="/login",
     * summary="Login",
     * description="Login by emailOrName, password",
     * operationId="authLogin",
     * tags={"Auth"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"emailOrName","password"},
     *       @OA\Property(property="emailOrName", type="string", format="name", example="example@example.com"),
     *       @OA\Property(property="password", type="string", format="password", example="123456"),
     *    ),
     * ),
     * @OA\Response(
     *    response=401,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="result", type="string", example="error"),
     *       @OA\Property(property="error", type="string", example="User does not exist")
     *    )
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *       @OA\Property(property="result", type="string", example="success"),
     *       @OA\Property(property="api_token", type="string", example="OzQ50ke3GElJMNvBZm8uksngp8dqNVYAHqr5CGHN9visYI0TYHg1fFdhsNf8BqTpwqDwXqcPhcxzN3Pj")
     *        )
     *     )
     * )
     * @param Request $request
     * @return JsonResponse
     */
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

    /**
     * @OA\Post(
     * path="/register",
     * summary="Register",
     * description="Register by name, email, password",
     * operationId="authRegister",
     * tags={"Auth"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"name", "email", "password"},
     *       @OA\Property(property="email", type="string", format="email", example="example@example.com"),
     *       @OA\Property(property="name", type="string", format="name", example="example"),
     *       @OA\Property(property="password", type="string", format="password", example="123456"),
     *    ),
     * ),
     * @OA\Response(
     *    response=404,
     *    description="Name Or Email has already been taken",
     *    @OA\JsonContent(
     *       @OA\Property(property="result", type="string", example="error"),
     *       @OA\Property(property="error", type="string", example="The name has already been taken.")
     *    )
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *       @OA\Property(property="result", type="string", example="success"),
     *       @OA\Property(property="api_token", type="string", example="Registration completed successfully")
     *        )
     *     )
     * )
     * @param Request $request
     * @return JsonResponse
     */
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
            return $this->sendError($validator->errors()->first());
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
