<?php

namespace App\Http\Controllers;

use App\Messages\Messages;
use App\Models\Code;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class MailerController extends \App\Http\Controllers\API\Controller
{
    /**
     * @OA\Post(
     * path="/sendLetter",
     * summary="sendLetter",
     * description="sendLetter by email",
     * operationId="sendLetter",
     * tags={"Mailer"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Mailer",
     *    @OA\JsonContent(
     *       required={"email"},
     *       @OA\Property(property="email", type="string", example="sergei.pokhodai@gmail.com")
     *    ),
     * ),
     * @OA\Response(
     *    response=401,
     *    description="Wrong response",
     *    @OA\JsonContent(
     *       @OA\Property(property="result", type="string", example="error"),
     *       @OA\Property(property="error", type="string", example="All fields are mandatory")
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *       @OA\Property(property="result", type="string", example="success"),
     *       @OA\Property(property="success", type="string", example="Verification code sent to sergei.pokhodai@gmail.com")
     *     )
     *   )
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function sendLetter(Request $request): JsonResponse
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'email' => 'required|string'
        ]);
        if ($validator->fails()) {
            return $this->sendError(Messages::allFieldsError);
        }
        $verification = Code::where('email', $request['email'])->first();
        if ($verification) {
            Code::where('email', $request['email'])
                ->take(1)
                ->delete();
        }
        $code = rand(999, 9999);
        $user = User::where('email', $input['email'])->first();
        if (!$user) {
            return $this->sendError(Messages:: mailSearchError);
        }
        $message = 'Dear ' . $user['your_name'] . ', this is your personal 4-digit confirmation code. Do not share this code with anyone. Confirmation code: ' . $code;
        Mail::raw($message, function ($message) use ($input) {
            $message->to($input['email'])->subject("Password recovery");
            $message->from('mriyadev@gmail.com', 'Pumba');
        });
        Code::forceCreate([
            'email' => $input['email'],
            'code' => $code
        ]);
        return $this->sendSuccess('Verification code sent to ' . $input['email']);
    }

    /**
     * @OA\Post(
     * path="/checkCode",
     * summary="checkCode",
     * description="check by code",
     * operationId="checkCode",
     * tags={"Mailer"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Code",
     *    @OA\JsonContent(
     *       required={"code"},
     *       @OA\Property(property="code", type="string", example="1234")
     *    ),
     * ),
     * @OA\Response(
     *    response=401,
     *    description="Wrong response",
     *    @OA\JsonContent(
     *       @OA\Property(property="result", type="string", example="error"),
     *       @OA\Property(property="error", type="string", example="All fields are mandatory")
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *       @OA\Property(property="result", type="string", example="success"),
     *       @OA\Property(property="success", type="string", example="Code is correct")
     *     )
     *   )
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function checkCode(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|min:4'
        ]);
        if ($validator->fails()) {
            return $this->sendError(Messages::allFieldsError);
        } else {
            $verification = Code::where('code', $request['code'])->first();
            if (!$verification) {
                return $this->sendError(Messages::codeError);
            }
            Code::where('code', $request['code'])
                ->take(1)
                ->delete();
            return $this->sendSuccess(Messages::codeSuccess);
        }
    }
}
