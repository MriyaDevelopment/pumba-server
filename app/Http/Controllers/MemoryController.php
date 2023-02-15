<?php


namespace App\Http\Controllers;

use App\Messages\Messages;
use App\Models\Guide;
use App\Models\Memory;
use Exception;
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
     *       @OA\Property(property="date", type="string"),
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
    public function add(Request $request): JsonResponse
    {

        $api_token = $this->getApiToken($request);

        $validator = Validator::make($request->all(), [
            'childId' => 'required|string',
            'image' => 'required|string',
            'name' => 'required|string',
            'note' => 'required|string',
            'color' => 'required|string',
            'date' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError(Messages::allFieldsError);
        }

        if (!$this->getUserByToken($api_token)) {
            return $this->sendError(Messages::userError);
        }

        $image = "";
        if (!$this->stringIsEmptyOrNull($request['image'])) {
            $image = $this->uploadImage($request['image']);
        }

        try {
            Memory::forceCreate([
                'childId' => $request['childId'],
                'api_token' => $api_token,
                'name' => $request['name'],
                'note' => $request['note'],
                'color' => $request['color'],
                'date' => $request['date'],
                'image' => $image
            ]);

        } catch (Exception $exception) {
            return $this->sendFailure($request, $exception, method: "/addMemory");
        }

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
    public function get(Request $request): JsonResponse
    {
        $api_token = $this->getApiToken($request);

        $validator = Validator::make($request->all(), [
            'childId' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError(Messages::allFieldsError);
        }

        if (!$this->getUserByToken($api_token)) {
            return $this->sendError(Messages::userError);
        }

        try {
            $memories = Memory::where('childId', $request['childId'])->get();

        } catch (Exception $exception) {
            return $this->sendFailure($request, $exception, method: "/getMemories");
        }

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
    public function delete(Request $request): JsonResponse
    {
        $api_token = $this->getApiToken($request);

        $validator = Validator::make($request->all(), [
            'id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError(Messages::allFieldsError);
        }

        if (!$this->getUserByToken($api_token)) {
            return $this->sendError(Messages::userError);
        }

        try {
            Memory::where('id', $request['id'])->delete();

        } catch (Exception $exception) {
            return $this->sendFailure($request, $exception, method: "/deleteMemory");
        }

        return $this->sendSuccess(Messages::memoryDeleteSuccess);
    }

    /**
     * @OA\Post(
     * path="/editMemory",
     * summary="editMemory",
     * description="Изменение идет по id самого memory, в данном случае api_token проверяет существование пользователя в данный момент, все поля кроме id могут являться опционально null или empty, если вы укажите просто id а другие поля оставите неизменными, empty, null то ничего не произайдет, если поменяется поле и оно будет не null, notEmpty и не равно значению поля в таблице то оно обновиться и перезапишется",
     * operationId="editMemory",
     * security={
     * {"Authorization": {}}},
     * tags={"Memories"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"id"},
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="note", type="string"),
     *       @OA\Property(property="color", type="string"),
     *       @OA\Property(property="image", type="string"),
     *       @OA\Property(property="date", type="string"),
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
    public function edit(Request $request): JsonResponse
    {

        $api_token = $this->getApiToken($request);

        $validator = Validator::make($request->all(), [
            'id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError(Messages::allFieldsError);
        }

        if (!$this->getUserByToken($api_token)) {
            return $this->sendError(Messages::userError);
        }

        try {
            $memory = Memory::where('id', $request['id'])->first();

            if (!$memory) {
                return $this->sendError(Messages::memoryError);
            }

            if (!$this->stringIsEmptyOrNull($request['name']) && $request['name'] != $memory['name']) {
                $memory->name = $request['name'];
                $memory->save();
            }

            if (!$this->stringIsEmptyOrNull($request['date']) && $request['date'] != $memory['date']) {
                $memory->date = $request['date'];
                $memory->save();
            }

            if (!$this->stringIsEmptyOrNull($request['note']) && $request['note'] != $memory['note']) {
                $memory->note = $request['note'];
                $memory->save();
            }

            if (!$this->stringIsEmptyOrNull($request['color']) && $request['color'] != $memory['color']) {
                $memory->color = $request['color'];
                $memory->save();
            }

            if (!$this->stringIsEmptyOrNull($request['image']) && $request['image'] != $memory['image']) {
                $memory->image = $this->uploadImage($request['image']);
                $memory->save();
            }

        } catch (Exception $exception) {
            return $this->sendFailure($request, $exception, method: "/editMemory");
        }

        return $this->sendSuccess(Messages::memoryEditedSuccess);
    }
}
