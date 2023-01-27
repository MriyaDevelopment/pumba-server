<?php


namespace App\Http\Controllers;

use App\Messages\Messages;
use App\Models\Guide;
use App\Models\Memory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MemoryController extends \App\Http\Controllers\API\Controller
{
    /**
     * @OA\Post(
     * path="/addMemory",
     * summary="addMemory",
     * description="addMemory by api_token, childId, image, note, name, color",
     * operationId="addMemory",
     * security={
     * {"Authorization": {}}},
     * tags={"Memories"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"name", "note", "image", "childId", "color"},
     *       @OA\Property(property="name", type="string", example="Example"),
     *       @OA\Property(property="note", type="string"),
     *       @OA\Property(property="color", type="string"),
     *       @OA\Property(property="image", type="string", example="Base64 or Null(удали поле опционально null/nil)"),
     *       @OA\Property(property="childId", type="string"),
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
     *       @OA\Property(property="success", type="string", example="Memory added successfully")
     *     )
     *   )
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function add(Request $request): JsonResponse {

        $api_token = substr($request->headers->get('Authorization', ''), 7);

        $validator = Validator::make($request->all(), [
            'childId' => 'required|string',
            'image' => 'required|string',
            'name' => 'required|string',
            'note' => 'required|string',
            'color' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError(Messages::allFieldsError);
        }

        if (!$this->getUserByToken($api_token)) {
            return $this->sendError(Messages::userError);
        }

        $requestImage = $request['image'] ?: "requestNull";
        $requestImage = $requestImage == "requestNull" ? null : $this->uploadImage($requestImage);

        Memory::forceCreate([
            'childId' => $request['childId'],
            'api_token' => $api_token,
            'name' => $request['name'],
            'note' => $request['note'],
            'color' => $request['color'],
            'image' => $requestImage
        ]);

        return $this->sendSuccess(Messages::memoryAddedSuccess);
    }

    /**
     * @OA\Post(
     * path="/getMemories",
     * summary="Memories",
     * description="Memories by api_token, childId",
     * operationId="memories",
     * security={
     * {"Authorization": {}}},
     * tags={"Memories"},
     * @OA\RequestBody(
     *    required=true,
     *    description="child id",
     *    @OA\JsonContent(
     *       required={"childId"},
     *       @OA\Property(property="childId", type="string", example="0"),
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
     *       @OA\Property(property="memories", type="array",
     *       @OA\Items(type="object",
     *       @OA\Property(property="id", type="string"),
     *       @OA\Property(property="date", type="string"),
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="note", type="string"),
     *       @OA\Property(property="color", type="string"),
     *       @OA\Property(property="image", type="string"),
     *       @OA\Property(property="childId", type="string"),
     *       )
     *      )
     *     )
     *   )
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function get(Request $request): JsonResponse {
        $api_token = substr($request->headers->get('Authorization', ''), 7);

        $validator = Validator::make($request->all(), [
            'childId' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError(Messages::allFieldsError);
        }

        if (!$this->getUserByToken($api_token)) {
            return $this->sendError(Messages::userError);
        }

        $memories = Memory::where('childId', $request['childId'])->get();

        return $this->sendResponse($memories, 'memories');
    }

    /**
     * @OA\Post(
     * path="/deleteMemory",
     * summary="deleteMemory",
     * description="deleteMemory by api_token, id",
     * operationId="deleteMemory",
     * security={
     * {"Authorization": {}}},
     * tags={"Memories"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"id"},
     *       @OA\Property(property="id", type="string"),
     *    ),
     * ),
     * @OA\Response(
     *    response=401,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="result", type="string", example="error"),
     *       @OA\Property(property="error", type="string", example="Memory does not exist")
     *    )
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *       @OA\Property(property="result", type="string", example="success"),
     *       @OA\Property(property="success", type="string", example="Memory deleted successfully")
     *     )
     *   )
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse {

        $api_token = substr($request->headers->get('Authorization', ''), 7);

        $validator = Validator::make($request->all(), [
            'id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError(Messages::allFieldsError);
        }

        if (!$this->getUserByToken($api_token)) {
            return $this->sendError(Messages::userError);
        }

        Memory::where('id', $request['id'])->delete();

        return $this->sendSuccess(Messages::memoryDeleteSuccess);
    }

    /**
     * @OA\Post(
     * path="/editMemory",
     * summary="editMemory",
     * description="editMemory by id, image, note, name, color",
     * operationId="editMemory",
     * security={
     * {"Authorization": {}}},
     * tags={"Memories"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"id", "name", "note", "image", "color"},
     *       @OA\Property(property="name", type="string", example="Example"),
     *       @OA\Property(property="note", type="string"),
     *       @OA\Property(property="color", type="string"),
     *       @OA\Property(property="image", type="string", example="Base64 or Null(удали поле опционально null/nil)"),
     *       @OA\Property(property="id", type="string"),
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
     *       @OA\Property(property="success", type="string", example="Memory eddited successfully")
     *     )
     *   )
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function edit(Request $request): JsonResponse {

        $api_token = substr($request->headers->get('Authorization', ''), 7);

        $validator = Validator::make($request->all(), [
            'id' => 'required|string',
            'image' => 'required|string',
            'name' => 'required|string',
            'note' => 'required|string',
            'color' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError(Messages::allFieldsError);
        }

        if (!$this->getUserByToken($api_token)) {
            return $this->sendError(Messages::userError);
        }

        $memory = Memory::where('id', $request['id'])->firts();

        $requestImage = $request['image'] ?: "requestNull";

        if ($request['name'] != $memory['name']) {
            $memory ->name = $request['name'];
            $memory->save();
        }

        if ($request['note'] != $memory['note']) {
            $memory ->note = $request['note'];
            $memory->save();
        }

        if ($request['color'] != $memory['color']) {
            $memory->color = $request['color'];
            $memory->save();
        }

        if ($request['image'] != $memory['image']) {
            $memory->image = $requestImage == "requestNull" ? null : $this->uploadImage($requestImage);
            $memory->save();
        }

        return $this->sendSuccess(Messages::memoryEditedSuccess);
    }
}
