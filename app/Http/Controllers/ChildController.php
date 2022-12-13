<?php


namespace App\Http\Controllers;

use App\Messages\Messages;
use App\Models\Child;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\Controller;

class ChildController extends Controller
{
    /**
     * @OA\Post(
     * path="/getChildren",
     * summary="Children",
     * description="Children by api_token",
     * operationId="children",
     * tags={"Child"},
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
        $validator = Validator::make($request->all(), [
            'api_token' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError(Messages::allFieldsError);
        }

        $api_token = $request['api_token'];

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
     * description="editChild by api_token, name, avatar, id, gender, birth",
     * operationId="editChild",
     * tags={"Child"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"api_token", "name", "id", "gender", "birth"},
     *       @OA\Property(property="id", type="string", example=0),
     *       @OA\Property(property="api_token", type="string", example="OzQ50ke3GElJMNvBZm8uksngp8dqNVYAHqr5CGHN9visYI0TYHg1fFdhsNf8BqTpwqDwXqcPhcxzN3Pj"),
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
        $validator = Validator::make($request->all(), [
            'api_token' => 'required|string',
            'id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return $this->sendError(Messages::allFieldsError);
        }

        $api_token = $request['api_token'];

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
     * description="deleteChild by api_token, id",
     * operationId="deleteChild",
     * tags={"Child"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"api_token"},
     *       @OA\Property(property="id", type="string", example=0),
     *       @OA\Property(property="api_token", type="string", example="OzQ50ke3GElJMNvBZm8uksngp8dqNVYAHqr5CGHN9visYI0TYHg1fFdhsNf8BqTpwqDwXqcPhcxzN3Pj")
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
    private function delete(Request $request): JsonResponse
    {

        $validator = Validator::make($request->all(), [
            'api_token' => 'required|string',
            'id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError(Messages::allFieldsError);
        }

        $api_token = $request['api_token'];

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
     * description="addChild by api_token, name, avatar, gender, birth",
     * operationId="addChild",
     * tags={"Child"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"api_token", "name", "gender", "birth"},
     *       @OA\Property(property="api_token", type="string", example="OzQ50ke3GElJMNvBZm8uksngp8dqNVYAHqr5CGHN9visYI0TYHg1fFdhsNf8BqTpwqDwXqcPhcxzN3Pj"),
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
    private function add(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'api_token' => 'required|string',
            'name' => 'required|string',
            'gender' => 'required|string',
            'birth' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError(Messages::allFieldsError);
        }

        $api_token = $request['api_token'];

        $user = $this->getUserByToken($api_token);

        if (!$user) {
            return $this->sendError(Messages::userError);
        }

        $requestName = $request['name'];
        $requestGender = $request['gender'];
        $requestBirth = $request['birth'];

        $requestAvatar = $request['avatar'] ?: "requestNull";
        $requestAvatar = $requestAvatar == "requestNull" ? null : $this->uploadImage($requestAvatar);

        Child::forceCreate([
            'name' => $requestName,
            'gender' => $requestGender,
            'birth' => $requestBirth,
            'avatar' => $requestAvatar,
            'api_token' => $api_token
        ]);

        return $this->sendSuccess(Messages::childAddedSuccess);
    }
}
