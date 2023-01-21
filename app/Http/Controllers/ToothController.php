<?php

namespace App\Http\Controllers;

use App\Messages\Messages;
use App\Models\Tooth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ToothController extends \App\Http\Controllers\API\Controller
{

    /**
     * @OA\Post(
     * path="/getDropedTeeth",
     * summary="Teeth",
     * description="Teeth by api_token, childId",
     * operationId="teeth",
     * tags={"Teeth"},
     * security={
     * {"Authorization": {}}},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"childId"},
     *       @OA\Property(property="childId", type="string", example="19")
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
     *       @OA\Property(property="teeth", type="array",
     *       @OA\Items(
     *       @OA\Property(property="tooth", type="object", example= {"toothId" : "22", "childId" : "55", "id" : "49" })
     *     )
     *    )
     *   )
     *   )
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function get(Request $request): JsonResponse
    {
        $api_token = substr($request->headers->get('Authorization', ''), 7);

        if (!$this->getUserByToken($api_token)) {
            return $this->sendError(Messages::userError);
        }

        $validator = Validator::make($request->all(), [
            'childId' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError(Messages::allFieldsError);
        }

        $tooth = Tooth::where('api_token', $api_token)->where('childId', $request['childId'])->get();

        return $this->sendResponse($tooth, 'tooth');
    }

    /**
     * @OA\Post(
     * path="/setDropedTooth",
     * summary="setDropedTooth",
     * description="setDropedTooth by api_token, childId, toothId",
     * operationId="tooth",
     * tags={"Teeth"},
     * security={
     * {"Authorization": {}}},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"childId", "toothId"},
     *       @OA\Property(property="childId", type="string", example="2"),
     *       @OA\Property(property="toothId", type="string", example="12"),
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
     *       @OA\Property(property="toothInfo", type="object",
     *       @OA\Property(property="toothId", type="string", example="12"),
     *       @OA\Property(property="isDroped", type="boolean", example=false),
     *       )
     *     )
     *   )
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function set(Request $request): JsonResponse
    {
        $api_token = substr($request->headers->get('Authorization', ''), 7);

        if (!$this->getUserByToken($api_token)) {
            return $this->sendError(Messages::userError);
        }

        $validator = Validator::make($request->all(), [
            'childId' => 'required|string',
            'toothId' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError(Messages::allFieldsError);
        }

        $tooth = Tooth::where('api_token', $api_token)
            ->where('childId', $request['childId'])
            ->where('toothId', $request['toothId'])
            ->first();

        $isDroped = false;

        if ($tooth) {
            $tooth->delete();
        } else {
            Tooth::forceCreate([
                'childId' => $request['childId'],
                'toothId' => $request['toothId'],
                'api_token' => $api_token
            ]);
            $isDroped = true;
        }

        $toothInfo = [
            'toothId' => $request['toothId'],
            'isDroped' => $isDroped
        ];

        return $this->sendResponse($toothInfo, 'toothInfo');
    }
}
