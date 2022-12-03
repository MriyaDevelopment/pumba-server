<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\User;
use http\Env\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Storage;
use PhpParser\Node\Scalar\String_;
use Psy\Util\Str;

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

    public function sendSuccess($success, $code = 200): JsonResponse
    {
        $response = [
            'result' => 'success',
            'success' => $success
        ];
        return response()->json($response, $code);
    }

    public function getUserByToken(String $api_token) {
        return User::where('api_token', $api_token)->first();
    }

    public function getChildrenByToken(String $api_token)  {
        return Child::where('api_token', $api_token)->get();
    }

    public function uploadImage(String $imageForBase64): String {
        $image = str_replace('data:image/png;base64,', '', $imageForBase64);
        $image = str_replace(' ', '+', $image);
        $name = time() . '.png';
        Storage::disk('local')->put($name, base64_decode($image));
        return $name;
    }
}
