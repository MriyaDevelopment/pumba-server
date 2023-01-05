<?php

namespace App\Http\Controllers;

use App\Messages\Messages;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FCMController extends \App\Http\Controllers\API\Controller
{
    /**
     * @OA\Post(
     * path="/updateFcmToken",
     * summary="updateFcmToken",
     * description="updateFcmToken by api_token, fcm_token",
     * operationId="updateFcmToken",
     * tags={"FCM"},
     * @OA\RequestBody(
     *    required=true,
     *    description="fcm",
     *    @OA\JsonContent(
     *       required={"api_token","fcm_token"},
     *       @OA\Property(property="api_token", type="string", example="OzQ50ke3GElJMNvBZm8uksngp8dqNVYAHqr5CGHN9visYI0TYHg1fFdhsNf8BqTpwqDwXqcPhcxzN3Pj"),
     *       @OA\Property(property="fcm_token", type="string", example="OzQ50ke3GElJMNvBZm8uksngp8dqNVYAHqr5CGHN9visYI0TYHg1fFdhsNf8BqTpwqDwXqcPhcxzN3Pj"),
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
     *       @OA\Property(property="success", type="string", example="fcm_token updated success")
     *     )
     *   )
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function updateFcmToken(Request $request): JsonResponse
    {
        $user = $this->getUserByToken($request['api_token']);
        if (!$user) {
            return $this->sendError(Messages::userError);
        }
        $user->fcm_token = $request['fcm_token'];
        $user->save();
        return $this->sendSuccess(Messages::fcmUpdatedSuccess);
    }

    /**
     * @OA\Post(
     * path="/sendTestFCMMessage",
     * summary="sendTestFCMMessage",
     * description="sendTestFCMMessage by api_token",
     * operationId="sendTestFCMMessage",
     * tags={"FCM"},
     * @OA\RequestBody(
     *    required=true,
     *    description="fcm",
     *    @OA\JsonContent(
     *       required={"api_token", "body", "title"},
     *       @OA\Property(property="api_token", type="string", example="OzQ50ke3GElJMNvBZm8uksngp8dqNVYAHqr5CGHN9visYI0TYHg1fFdhsNf8BqTpwqDwXqcPhcxzN3Pj"),
     *       @OA\Property(property="body", type="string", example="Test"),
     *       @OA\Property(property="title", type="string", example="Test"),
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
     *       @OA\Property(property="success", type="string", example="Testing Notification Successfully")
     *     )
     *   )
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function sendTestFCMMessage(Request $request): JsonResponse {
        $user = $this->getUserByToken($request['api_token']);
        if (!$user) {
            return $this->sendError(Messages::userError);
        }
        self::send(
            $user['fcm_token'],
            [
                'title' => $request['title'],
                'body' => $request['body'],
                'sound' => 'default'
            ]
        );
        $result = [
          'api_token' => $user['api_token'],
          'fcm_token' => $user['fcm_token'],
          'title' => $request['title'],
          'body' => $request['body']
        ];
        return $this->sendResponse($result, 'result');
    }

    public function send($token, $notification)
    {
        Http::acceptJson()->withToken(config('fcm.token'))->post(
            'https://fcm.googleapis.com/fcm/send',
            [
                'to' => $token,
                'notification' => $notification,
            ]
        );
    }
}
