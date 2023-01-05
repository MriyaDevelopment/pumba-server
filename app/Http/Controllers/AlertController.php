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
     * path="/sendMessage",
     * summary="sendMessage",
     * description="sendMessage by api_token, text",
     * operationId="sendMessage",
     * tags={"AlertManager"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Бот воспринимает только text = английский",
     *    @OA\JsonContent(
     *       required={"api_token", "text"},
     *       @OA\Property(property="api_token", type="string", example="OzQ50ke3GElJMNvBZm8uksngp8dqNVYAHqr5CGHN9visYI0TYHg1fFdhsNf8BqTpwqDwXqcPhcxzN3Pj"),
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
    public function sendMessage(Request $request): JsonResponse {

        $user = $this->getUserByToken($request['api_token']);
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

        return $this->sendSuccess("Bot sent a message");
    }
}
