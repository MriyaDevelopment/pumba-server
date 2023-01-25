<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\ChildController;
use App\Http\Controllers\FCMController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\MailerController;
use App\Http\Controllers\MemoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\ToothController;
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
Route::post('changePassword', [AuthController::class, 'changePassword']);
Route::post('changeEmail', [AuthController::class, 'changeEmail']);
Route::post('loginOrRegisterViaSocialNetworks', [AuthController::class, 'loginOrRegisterViaSocialNetworks']);

//Profile
Route::post('getProfile', [ProfileController::class, 'get']);
Route::post('deleteProfile', [ProfileController::class, 'delete']);
Route::post('editProfile', [ProfileController::class, 'edit']);
Route::post('setResultQuiz', [ProfileController::class, 'addFiltersByGames']);

//Child
Route::post('getChildren', [ChildController::class, 'get']);
Route::post('editChild', [ChildController::class, 'edit']);
Route::post('deleteChild', [ChildController::class, 'delete']);
Route::post('addChild', [ChildController::class, 'add']);

//Guide
Route::post('getSubCategoryGuides', [GuideController::class, 'get']);

//Reminder
Route::post('getReminders', [ReminderController::class, 'get']);
Route::post('addReminder', [ReminderController::class, 'add']);
Route::post('deleteReminder', [ReminderController::class, 'delete']);
Route::post('editReminder', [ReminderController::class, 'edit']);

//Memories
Route::post('getMemories', [MemoryController::class, 'get']);
Route::post('addMemory', [MemoryController::class, 'add']);
Route::post('editMemory', [MemoryController::class, 'edit']);
Route::post('deleteMemory', [MemoryController::class, 'delete']);

//Games
Route::post('getGames', [GameController::class, 'get']);
Route::post('saveGame', [GameController::class, 'save']);
Route::post('getGameById', [GameController::class, 'getById']);
Route::post('getSavedGames', [GameController::class, 'getSavedGames']);

//Teeth
Route::post('getDropedTeeth', [ToothController::class, 'get']);
Route::post('setDropedTooth', [ToothController::class, 'set']);

//Mailer
Route::post('sendLetter', [MailerController::class, 'sendLetter']);
Route::post('checkCode', [MailerController::class, 'checkCode']);

//FCM
Route::post('updateFcmToken', [FCMController::class, 'updateFcmToken']);
Route::post('sendTestFCMMessage', [FCMController::class, 'sendTestFCMMessage']);

//TELEGRAM BOT
Route::post('sendAlert', [AlertController::class, 'sendAlert']);
