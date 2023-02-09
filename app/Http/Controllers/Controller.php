<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Ramsey\Uuid\Type\Integer;


class Controller extends BaseController
{
    /**
     * success response method.
     *
     * @return JsonResponse
     */
    public function sendResponse($result, $name): JsonResponse
    {
        $response = [
            'result' => 'success',
            $name => $result,
        ];
        return response()->json($response, 200);
    }

    /**
     * return error response.
     *
     * @return JsonResponse
     */
    public function sendError($error, $errorMessages = [], $code = 404): JsonResponse
    {
        $response = [
            'result' => 'error',
            'error' => $error,
        ];
        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }
        return response()->json($response, $code);
    }

    public function sendFailure(Request $request = null, $failure, $code = 500, $method = ""): JsonResponse {

        $description = "МЕНЯ ТРАХНУЛИ тут ===> $method\n\nCode : $code\n\n$request\n\nFailure : $failure";

        Http::post('https://api.tlgr.org/bot5906683048:AAHrBp6aWLbbNX9V4puNHbvMSTDQYZERPyM/sendMessage', [
            'chat_id' => '-1001752492520',
            'text' => $description
        ]);

        report($failure);
        return response()->json($failure, $code);
    }

    public function sendSuccess($success, $code = 200): JsonResponse
    {
        $response = [
            'result' => 'success',
            'success' => $success
        ];
        return response()->json($response, $code);
    }
}
