<?php

namespace App\Http\Controllers;

use App\Messages\Messages;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AlertController extends  \App\Http\Controllers\API\Controller
{
    /**
     * @OA\Post(
     * path="/sendAlert",
     * summary="sendAlert",
     * description="sendAlert by (Header - api_token), text",
     * operationId="sendAlert",
     * security={
     * {"Authorization": {}}
     *       },
     * tags={"AlertManager"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Бот воспринимает только text = английский",
     *    @OA\JsonContent(
     *       required={"text"},
     *       @OA\Property(property="text", type="string", example="Error"),
     *    ),
     * ),
     * @OA\Response(
     *    response=401,
     *    description="Wrong response",
     *    @OA\JsonContent(
     *       @OA\Property(property="result", type="string", example="error"),
     *       @OA\Property(property="error", type="string", example="User does not exist")
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *       @OA\Property(property="result", type="string", example="success"),
     *       @OA\Property(property="success", type="string", example="Bot sent a message")
     *     )
     *   )
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function sendAlert(Request $request): JsonResponse {

        $api_token = substr($request->headers->get('Authorization', ''), 7);
        $user = $this->getUserByToken($api_token);

        if (!$user) {
            return $this->sendError(Messages::userError);
        }

        $description = [
            'api_token' => $user['api_token'],
            'name' => $user['name'],
            'role' => $user['role'],
            'email' => $user['email'],
            'text' => $request['text']
        ];

        Http::post('https://api.tlgr.org/bot5906683048:AAHrBp6aWLbbNX9V4puNHbvMSTDQYZERPyM/sendMessage', [
            'chat_id' => '-1001752492520',
            'text' => $description
        ]);

        return $this->sendSuccess(Messages::alertSendSuccess);
    }
}
