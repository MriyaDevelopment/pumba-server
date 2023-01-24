<?php


namespace App\Http\Controllers;

use App\Messages\Messages;
use App\Models\Child;
use App\Models\Memory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ChildController extends \App\Http\Controllers\API\Controller
{
    /**
     * @OA\Post(
     * path="/getChildren",
     * summary="Children",
     * description="Children by api_token",
     * operationId="children",
     * security={
     * {"Authorization": {}}},
     * tags={"Child"},
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
     *       @OA\Property(property="children", type="array",
     *       @OA\Items(type="object",
     *       @OA\Property(property="id", type="string", example=0),
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="avatar", type="string", example=null),
     *       @OA\Property(property="birth", type="string", example="2022-09-02"),
     *       @OA\Property(property="api_token", type="string"),
     *       @OA\Property(property="gender", type="string", example="Enum(Boy, Girl, Neutral)"),
     *       )
     *      )
     *     )
     *   )
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function get(Request $request): JsonResponse
    {

        $api_token = substr($request->headers->get('Authorization', ''), 7);

        $user = $this->getUserByToken($api_token);

        if (!$user) {
            return $this->sendError(Messages::userError);
        }

        $children = $this->getChildrenByToken($api_token);

        return $this->sendResponse($children, 'children');
    }

    /**
     * @OA\Post(
     * path="/editChild",
     * summary="editChild",
     * description="editChild by name, avatar, id, gender, birth",
     * operationId="editChild",
     * security={
     * {"Authorization": {}}},
     * tags={"Child"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"name", "id", "gender", "birth"},
     *       @OA\Property(property="id", type="string", example=0),
     *       @OA\Property(property="name", type="string", example="Example"),
     *       @OA\Property(property="gender", type="string", example="Enum(Boy, Girl, Neutral)"),
     *       @OA\Property(property="birth", type="string", example="2022-09-08"),
     *       @OA\Property(property="avatar", type="string", example="1669926220.png or Base64 or Null(удали поле опционально null/nil)"),
     *    ),
     * ),
     * @OA\Response(
     *    response=401,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="result", type="string", example="error"),
     *       @OA\Property(property="error", type="string", example="Child does not exist")
     *    )
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *       @OA\Property(property="result", type="string", example="success"),
     *       @OA\Property(property="success", type="string", example="Child edited successfully")
     *     )
     *   )
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function edit(Request $request): JsonResponse
    {
        $api_token = substr($request->headers->get('Authorization', ''), 7);

        $validator = Validator::make($request->all(), [
            'id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError(Messages::allFieldsError);
        }

        $user = $this->getUserByToken($api_token);

        if (!$user) {
            return $this->sendError(Messages::userError);
        }

        $childId = $request['id'];

        $child = Child::where('id', $childId)->first();

        if (!$child) {
            return $this->sendError(Messages::childError);
        }

        $childName = $child['name'];
        $childGender = $child['gender'];
        $childBirth = $child['birth'];
        $childAvatar = $child['avatar'] ?: "childNull";

        $requestName = $request['name'];
        $requestGender = $request['gender'];
        $requestBirth = $request['birth'];
        $requestAvatar = $request['avatar'] ?: "requestNull";

        if ($childName != $requestName) {
            $child->name = $requestName;
            $child->save();
        }

        if ($childGender != $requestGender) {
            $child->gender = $requestGender;
            $child->save();
        }

        if ($childBirth != $requestBirth) {
            $child->birth = $requestBirth;
            $child->save();
        }

        if ($childAvatar != $requestAvatar) {
            $child->avatar = $requestAvatar == "requestNull" ? null : $this->uploadImage($requestAvatar);
            $child->save();
        }

        return $this->sendSuccess(Messages::childEditedSuccess);
    }

    /**
     * @OA\Post(
     * path="/deleteChild",
     * summary="deleteChild",
     * description="deleteChild by id",
     * operationId="deleteChild",
     * security={
     * {"Authorization": {}}},
     * tags={"Child"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"id"},
     *       @OA\Property(property="id", type="string", example=0)
     *    ),
     * ),
     * @OA\Response(
     *    response=401,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="result", type="string", example="error"),
     *       @OA\Property(property="error", type="string", example="Child does not exist")
     *    )
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *       @OA\Property(property="result", type="string", example="success"),
     *       @OA\Property(property="success", type="string", example="Child deleted successfully")
     *     )
     *   )
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $api_token = substr($request->headers->get('Authorization', ''), 7);

        $validator = Validator::make($request->all(), [
            'id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError(Messages::allFieldsError);
        }

        $user = $this->getUserByToken($api_token);

        if (!$user) {
            return $this->sendError(Messages::userError);
        }

        $childId = $request['id'];

        $child = Child::where('id', $childId)->first();

        if (!$child) {
            return $this->sendError(Messages::childError);
        }

        $child->take(1)->delete();

        return $this->sendSuccess(Messages::childDeleteSuccess);
    }

    /**
     * @OA\Post(
     * path="/addChild",
     * summary="addChild",
     * description="addChild by name, avatar, gender, birth",
     * operationId="addChild",
     * security={
     * {"Authorization": {}}},
     * tags={"Child"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"name", "gender", "birth"},
     *       @OA\Property(property="name", type="string", example="Example"),
     *       @OA\Property(property="gender", type="string", example="Enum(Boy, Girl, Neutral)"),
     *       @OA\Property(property="birth", type="string", example="2022-09-08"),
     *       @OA\Property(property="avatar", type="string", example="Base64 or Null(удали поле опционально null/nil)"),
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
     *       @OA\Property(property="success", type="string", example="Child added successfully")
     *     )
     *   )
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function add(Request $request): JsonResponse
    {
        $api_token = substr($request->headers->get('Authorization', ''), 7);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'gender' => 'required|string',
            'birth' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError(Messages::allFieldsError);
        }

        $user = $this->getUserByToken($api_token);

        if (!$user) {
            return $this->sendError(Messages::userError);
        }

        $requestName = $request['name'];
        $requestGender = $request['gender'];
        $requestBirth = $request['birth'];

        $requestAvatar = $request['avatar'] ?: "requestNull";
        $requestAvatar = $requestAvatar == "requestNull" ? null : $this->uploadImage($requestAvatar);

        $child = Child::forceCreate([
            'name' => $requestName,
            'gender' => $requestGender,
            'birth' => $requestBirth,
            'avatar' => $requestAvatar,
            'api_token' => $api_token
        ]);

        $memories = [
            "Sleep in my bedroom",
            "Hey! It’s my first step",
            "Walk in park",
            "Go to the zoo",
            "Play with my favourite toys"
        ];

        foreach ($memories as $memory) {
            Memory::forceCreate([
                'name' => $memory,
                'childId' => $child['id'],
                'api_token' => $api_token
            ]);
        }

        return $this->sendSuccess(Messages::childAddedSuccess);
    }
}
