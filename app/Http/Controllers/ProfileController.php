<?php


namespace App\Http\Controllers;
use App\Messages\Messages;
use App\Models\Child;
use App\Models\Memory;
use App\Models\Reminder;
use App\Models\Tooth;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\API\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class ProfileController extends Controller
{
    /**
     * @OA\Post(
     * path="/getProfile",
     * summary="Profile",
     * description="Profile by api_token",
     * operationId="profile",
     * security={
     * {"Authorization": {}}},
     * tags={"Profile"},
     * @OA\Response(
     *    response=401,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="result", type="string", example="error"),
     *       @OA\Property(property="error", type="string", example="Profile does not exist")
     *    )
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *       @OA\Property(property="result", type="string", example="success"),
     *       @OA\Property(property="profile", type="object", example= {"name" : "ExampleName", "email" : "example@example.com", "fcm_token" : null, "api_token" : "string", "avatar" : "1669926220.png", "role" : "Enum(Mother, Father, Other)" })
     *     )
     *   )
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function get(Request $request): JsonResponse {

        try {
            $profile = $this->getUserByToken($this->getApiToken($request));

            if (!$profile) {
                return $this->sendError(Messages::profileError);
            }
        }catch (Exception $exception) {
            return $this->sendFailure($request, $exception, method: "/editProfile");
        }

        return $this->sendResponse($profile, 'profile');
    }

    /**
     * @OA\Post(
     * path="/editProfile",
     * summary="editProfile",
     * description="editProfile by api_token, name, avatar, role",
     * operationId="editProfile",
     * security={
     * {"Authorization": {}}},
     * tags={"Profile"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"name", "role"},
     *       @OA\Property(property="name", type="string", example="Example"),
     *       @OA\Property(property="role", type="string", example="Enum(Mother, Father, Other)"),
     *       @OA\Property(property="avatar", type="string", example="1669926220.png or Base64 or Null(удали поле опционально null/nil)"),
     *    ),
     * ),
     * @OA\Response(
     *    response=401,
     *    description="Wrong credentials response",
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
     *       @OA\Property(property="success", type="string", example="Profile edited successfully")
     *     )
     *   )
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function edit(Request $request): JsonResponse {

        $profile = $this->getUserByToken($this->getApiToken($request));

        if (!$profile) {
            return $this->sendError(Messages::profileError);
        }

        try {
            if (!$this->stringIsEmptyOrNull($request['name']) && $request['name'] != $profile['name']) {
                $profile->name = $request['name'];
                $profile->save();
            }

            if (!$this->stringIsEmptyOrNull($request['role']) && $request['role'] != $profile['role']) {
                $profile->role = $request['role'];
                $profile->save();
            }

            if (!$this->stringIsEmptyOrNull($request['avatar']) && $request['avatar'] != $profile['avatar']) {
                $profile->avatar = $this->uploadImage($request['avatar']);
                $profile->save();
            }
        } catch (Exception $exception) {
            return $this->sendFailure($request, $exception, method: "/editProfile");
        }

        return $this->sendSuccess(Messages::profileEditedSuccess);
    }

    /**
     * @OA\Post(
     * path="/deleteProfile",
     * summary="deleteProfile",
     * description="deleteProfile by api_token",
     * operationId="deleteProfile",
     * tags={"Profile"},
     * security={
     * {"Authorization": {}}},
     * @OA\Response(
     *    response=401,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="result", type="string", example="error"),
     *       @OA\Property(property="error", type="string", example="Profile does not exist")
     *    )
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *       @OA\Property(property="result", type="string", example="success"),
     *       @OA\Property(property="success", type="string", example="User account deleted successfully")
     *     )
     *   )
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse {

        $api_token = $this->getApiToken($request);

        try {
            $profile = $this->getUserByToken($api_token);

            if (!$profile) {
                return $this->sendError(Messages::profileError);
            }

            $profile->delete();

            $children = $this->getChildrenByToken($api_token);

            if ($children) {
                foreach ($children as $child) {
                    Child::where('id', $child['id'])->delete();
                }
            }

            $memories = Memory::where('api_token', $api_token)->get();

            if ($memories) {
                foreach ($memories as $memory) {
                    Memory::where('id', $memory['id'])->delete();
                }
            }

            $reminders = Reminder::where('api_token', $api_token)->get();

            if ($reminders) {
                foreach ($reminders as $reminder) {
                    Reminder::where('id', $reminder['id'])->delete();
                }
            }

            $memories = Memory::where('api_token', $api_token)->get();

            if ($memories) {
                foreach ($memories as $memory) {
                    Memory::where('id', $memory['id'])->delete();
                }
            }

            $teeth = Tooth::where('api_token', $api_token)->get();

            if ($teeth) {
                foreach ($teeth as $tooth) {
                    Memory::where('id', $tooth['id'])->delete();
                }
            }
        } catch (Exception $exception) {
            return $this->sendFailure($request, $exception, method: "/deleteProfile");
        }

        return $this->sendSuccess(Messages::profileDeleteSuccess);
    }

    /**
     * @OA\Post(
     * path="/setResultQuiz",
     * summary="setResultQuiz",
     * description="setResultQuiz by ages, energy_level, door_type, stuff, time",
     * operationId="setResultQuiz",
     * tags={"Profile"},
     * security={
     * {"Authorization": {}}},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"ages", "energy_level", "door_type", "stuff", "time"},
     *       @OA\Property(property="ages", type="string", example="0-1,2-4"),
     *       @OA\Property(property="energy_level", type="string", example="High"),
     *       @OA\Property(property="door_type", type="string", example="Indoor / door"),
     *       @OA\Property(property="stuff", type="string", example="Needed / Not needed"),
     *       @OA\Property(property="time", type="string", example="10-15,15-30"),
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
     *       @OA\Property(property="success", type="string", example="Filters added successfully")
     *     )
     *   )
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function addFiltersByGames(Request $request): JsonResponse {

        $profile = $this->getUserByToken($this->getApiToken($request));

        if (!$profile) {
            return $this->sendError(Messages::profileError);
        }

        $validator = Validator::make($request->all(), [
            'ages' => 'required|string',
            'energy_level' => 'required|string',
            'door_type' => 'required|string',
            'stuff' => 'required|string',
            'time' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError(Messages::allFieldsError);
        }

        try {
            $profile->ages = $request['ages'];
            $profile->energy_level = $request['energy_level'];
            $profile->door_type = $request['door_type'];
            $profile->stuff = $request['stuff'];
            $profile->time = $request['time'];
            $profile->save();
        } catch (Exception $exception) {
            return $this->sendFailure($request, $exception, method: "/setResultQuiz");
        }

        return $this->sendSuccess(Messages::profileFiltersAddSuccess);
    }
}
