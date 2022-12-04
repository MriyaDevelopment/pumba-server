<?php


namespace App\Http\Controllers;
use App\Messages\Messages;
use App\Models\Child;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\API\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * @OA\Post(
     * path="/getProfile",
     * summary="Profile",
     * description="Profile by api_token",
     * operationId="profile",
     * tags={"Profile"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Api token",
     *    @OA\JsonContent(
     *       required={"api_token"},
     *       @OA\Property(property="api_token", type="string", example="OzQ50ke3GElJMNvBZm8uksngp8dqNVYAHqr5CGHN9visYI0TYHg1fFdhsNf8BqTpwqDwXqcPhcxzN3Pj")
     *    ),
     * ),
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
    public function profile(Request $request): JsonResponse {

        $profile = $this->getUserByToken($request['api_token']);

        if (!$profile) {
            return $this->sendError(Messages::profileError);
        }

        return $this->sendResponse($profile, 'profile');
    }

    /**
     * @OA\Post(
     * path="/editProfile",
     * summary="editProfile",
     * description="editProfile by api_token, name, avatar, role",
     * operationId="editProfile",
     * tags={"Profile"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"api_token", "name", "role"},
     *       @OA\Property(property="api_token", type="string", example="OzQ50ke3GElJMNvBZm8uksngp8dqNVYAHqr5CGHN9visYI0TYHg1fFdhsNf8BqTpwqDwXqcPhcxzN3Pj"),
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

        $input = $request->all();

        $api_token = $input['api_token'];

        $rules = array(
            'name' => 'required|string|unique:users'
        );

        $messages = array(
            'name.required|string|unique:users' => Messages::userRegisterNameValidator,
        );

        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first());
        }

        $profile = $this->getUserByToken($api_token);

        if (!$profile) {
            return $this->sendError(Messages::profileError);
        }

        $profileName = $profile['name'];
        $profileAvatar = $profile['avatar'] ?: "profileNull";
        $profileRole = $profile['role'];

        $requestName = $request['name'];
        $requestAvatar = $request['avatar'] ?: "requestNull";
        $requestRole = $request['role'];

        if ($profileName != $requestName) {
            $profile->name = $requestName;
            $profile->save();
        }

        if ($profileRole != $requestRole) {
            $profile->role = $requestRole;
            $profile->save();
        }

        if ($profileAvatar != $requestAvatar) {
            $profile->avatar = $requestAvatar == "requestNull" ? null : $this->uploadImage($requestAvatar);
            $profile->save();
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
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"api_token"},
     *       @OA\Property(property="api_token", type="string", example="OzQ50ke3GElJMNvBZm8uksngp8dqNVYAHqr5CGHN9visYI0TYHg1fFdhsNf8BqTpwqDwXqcPhcxzN3Pj")
     *    ),
     * ),
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

        $api_token = $request['api_token'];

        $profile = $this->getUserByToken($api_token);

        if (!$profile) {
            return $this->sendError(Messages::profileError);
        }

        $profile->take(1)->delete();

        $children = $this->getChildrenByToken($api_token);

        foreach ($children as $child) {
            Child::where('id', $child['id'])->take(1)->delete();
        }

        return $this->sendSuccess(Messages::profileDeleteSuccess);
    }
}
