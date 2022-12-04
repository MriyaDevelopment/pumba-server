<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChildController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::get('storage/', function ($filename)
{
    $path = storage_path('public/' . $filename);

    if (!File::exists($path)) {
        abort(404);
    }

    $file = File::get($path);
    $type = File::mimeType($path);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return $response;
});


//Auth
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

//Profile
Route::post('getProfile', [ProfileController::class, 'profile']);
Route::post('deleteProfile', [ProfileController::class, 'delete']);
Route::post('editProfile', [ProfileController::class, 'edit']);

//Child
Route::post('getChildren', [ChildController::class, 'children']);
Route::post('editChild', [ChildController::class, 'edit']);
Route::post('deleteChild', [ChildController::class, 'delete']);
Route::post('addChild', [ChildController::class, 'add']);
