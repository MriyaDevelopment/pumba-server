<?php

namespace App\Http\Controllers;

use App\Http\Controllers\API\Controller;
use App\Messages\Messages;
use App\Models\User;
use Exception;
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
     * description="Login by email, password",
     * operationId="authLogin",
     * tags={"Auth"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"email","password"},
     *       @OA\Property(property="email", type="string", format="email", example="example@example.com"),
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
            'email' => 'required|string|max:255',
            'password' => 'required|string'
        );

        $validator = Validator::make($input, $rules);

        if ($validator->fails()) {
            return $this->sendError(Messages::allFieldsError);
        }

        $email = $input['email'];
        $password = $input['password'];

        try {
            $user = User::where('email', $email)->first();

            if (!$user) {
                return $this->sendError(Messages::userError);
            }

            if (!Hash::check($password, $user->password)) {
                return $this->sendError(Messages::userPasswordError);
            }
        }catch (Exception $exception) {
            return $this->sendFailure($request, $exception, method: "/login");
        }

        $api_token = $user['api_token'];

        return $this->sendResponse($api_token, 'api_token');
    }

    /**
     * @OA\Post(
     * path="/loginOrRegisterViaSocialNetworks",
     * summary="loginOrRegisterViaSocialNetworks",
     * description="LoginOrRegisterViaSocialNetworks by email, name",
     * operationId="LoginOrRegisterViaSocialNetworks",
     * tags={"Auth"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"email","name"},
     *       @OA\Property(property="email", type="string", format="email", example="example@example.com"),
     *       @OA\Property(property="name", type="string", format="name", example="example"),
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
    public function loginOrRegisterViaSocialNetworks(Request $request): JsonResponse {

        $rules = array(
            'email' => 'required|string|email|max:255|unique:users',
            'name' => 'required|string',
        );

        $messages = array(
            'email.required|string|email|max:255|unique:users' => Messages::userRegisterEmailValidator
        );

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return $this->sendError(Messages::allFieldsError);
        }

        try {
            $user = User::where('email', $request['email'])->first();

            if (!$user) {
                $user = User::forceCreate([
                    'email' => $request['email'],
                    'name' => $request['name'],
                    'api_token' => Str::random(80),
                ]);
            }
        }catch (Exception $exception) {
            return $this->sendFailure($request, $exception, method: "/loginOrRegisterViaSocialNetworks");
        }

        return $this->sendResponse($user['api_token'], 'api_token');
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
     *       @OA\Property(property="api_token", type="string", example="OzQ50ke3GElJMNvBZm8uksngp8dqNVYAHqr5CGHN9visYI0TYHg1fFdhsNf8BqTpwqDwXqcPhcxzN3Pj")
     *        )
     *     )
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function reg(Request $request): JsonResponse {

        $input = $request->all();

        $rules = array(
            'email' => 'required|string|email|max:255|unique:users',
            'name' => 'required|string',
            'password' => 'required|string'
        );

        $messages = array(
            'email.required|string|email|max:255|unique:users' => Messages::userRegisterEmailValidator
        );

        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first());
        }

        $email = $input['email'];
        $name = $input['name'];
        $password = Hash::make($input['password']);
        $api_token = Str::random(80);

        try {
            User::forceCreate([
                'email' => $email,
                'name' => $name,
                'password' => $password,
                'api_token' => $api_token,
            ]);
        }catch (Exception $exception) {
            return $this->sendFailure($request, $exception, method: "/register");
        }

        return $this->sendResponse($api_token, 'api_token');
    }

    /**
     * @OA\Post(
     * path="/changePassword",
     * summary="ChangePassword",
     * description="ChangePassword by email, password",
     * operationId="changePassword",
     * tags={"Auth"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"email", "password"},
     *       @OA\Property(property="email", type="string", format="email", example="example@example.com"),
     *       @OA\Property(property="password", type="string", format="password", example="123456"),
     *    ),
     * ),
     * @OA\Response(
     *    response=404,
     *    description="Mail does not exist",
     *    @OA\JsonContent(
     *       @OA\Property(property="result", type="string", example="error"),
     *       @OA\Property(property="error", type="string", example="Mail does not exist")
     *    )
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *       @OA\Property(property="result", type="string", example="success"),
     *       @OA\Property(property="api_token", type="string", example="Password changed successfully")
     *        )
     *     )
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function changePassword(Request $request): JsonResponse {
        $input = $request->all();
        $validator = Validator::make($input, [
            'email' => 'required|string|email',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return $this->sendError(Messages::allFieldsError);
        }
        try {
            $user = User::where('email', $input['email'])->first();
            if (!$user) {
                return $this->sendError(Messages::mailSearchError);
            }
            $user->password = Hash::make($input['password']);
            $user->save();
        } catch (Exception $exception) {
            return $this->sendFailure($request, $exception, method: "/changePassword");
        }
        return $this->sendSuccess(Messages::userChangePasswordSuccess);
    }

    /**
     * @OA\Post(
     * path="/changeEmail",
     * summary="ChangeEmail",
     * description="ChangeEmail by new_email, old_email",
     * operationId="ChangeEmail",
     * tags={"Auth"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"email", "old_email"},
     *       @OA\Property(property="email", type="string", format="email", example="example@example.com"),
     *       @OA\Property(property="old_email", type="string", format="email", example="example123@example.com"),
     *    ),
     * ),
     * @OA\Response(
     *    response=404,
     *    description="Mail does not exist",
     *    @OA\JsonContent(
     *       @OA\Property(property="result", type="string", example="error"),
     *       @OA\Property(property="error", type="string", example="Mail does not exist")
     *    )
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *       @OA\Property(property="result", type="string", example="success"),
     *       @OA\Property(property="api_token", type="string", example="Mail changed successfully")
     *        )
     *     )
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function changeEmail(Request $request): JsonResponse {
        $input = $request->all();

        $rules = array(
            'email' => 'required|string|email|max:255|unique:users',
            'old_email' => 'required|string'
        );
        $messages = array(
            'new_email.required|string|email|max:255|unique:users' => Messages::userRegisterEmailValidator
        );

        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first());
        }

        try {
            $user = User::where('email', $input['old_email'])->first();
            if (!$user) {
                return $this->sendError(Messages::mailSearchError);
            }
            $user->email = $input['email'];
            $user->save();
        } catch (Exception $exception) {
            return $this->sendFailure($request, $exception, method: "/changeEmail");
        }

        return $this->sendSuccess(Messages::userChangeMailSuccess);
    }
}
