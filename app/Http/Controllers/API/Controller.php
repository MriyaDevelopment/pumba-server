<?php


namespace App\Http\Controllers\API;


use App\Models\Child;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Info(
 *    title="Pumba Api",
 *    version="1.0.2",
 * )
 * * @OA\Server(
 *     description="Pumba",
 *     url="http://ovz5.j04713753.0n03n.vps.myjino.ru/public/api/"
 * )
 * @OA\SecurityScheme(
 *    securityScheme="Authorization",
 *    in="header",
 *    name="Authorization",
 *    type="http",
 *    scheme="bearer",
 *    bearerFormat="JWT",
 * ),
 */
class Controller extends \App\Http\Controllers\Controller
{
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
