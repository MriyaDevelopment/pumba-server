<?php


namespace App\Http\Controllers;

use App\Messages\Messages;
use App\Models\Guide;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GuideController extends \App\Http\Controllers\API\Controller
{
    /**
     * @OA\Post(
     * path="/getSubCategoryGuides",
     * summary="Guides",
     * description="Guides by category",
     * operationId="guide",
     * tags={"Guide"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Enum(Health, RelationShip, Sleep, Hygiene, Feeding, Learning) -> String",
     *    @OA\JsonContent(
     *       required={"category"},
     *       @OA\Property(property="category", type="string", example="Sleep")
     *    ),
     * ),
     * @OA\Response(
     *    response=401,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="result", type="string", example="error"),
     *       @OA\Property(property="error", type="string", example="Guide does not exist")
     *    )
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *       @OA\Property(property="result", type="string", example="success"),
     *       @OA\Property(property="guides", type="array",
     *       @OA\Items(type="object",
     *       @OA\Property(property="id", type="string", example="0"),
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="description", type="string", example="string")
     *       )
     *      )
     *     )
     *   )
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function get(Request $request): JsonResponse {

        $category = $request['category'];

        $guides = Guide::where('category', $category)->get();

        if (!$guides) {
            return $this ->sendError(Messages::guidesError);
        }

        return $this->sendResponse($guides, "guides");
    }
}
