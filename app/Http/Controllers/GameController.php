<?php

namespace App\Http\Controllers;

use App\Messages\Messages;
use App\Models\Game;
use App\Models\Guide;
use App\Models\Inventory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameController extends \App\Http\Controllers\API\Controller
{
    /**
     * @OA\Post(
     * path="/getGames",
     * summary="Games",
     * description="Games by api_token",
     * operationId="games",
     * tags={"Games"},
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
     *       @OA\Property(property="games", type="array",
     *       @OA\Items(type="object",
     *       @OA\Property(property="id", type="string"),
     *       @OA\Property(property="title", type="string"),
     *       @OA\Property(property="subtitle", type="string"),
     *       @OA\Property(property="type", type="string"),
     *       @OA\Property(property="time", type="string"),
     *       @OA\Property(property="image", type="string"),
     *       @OA\Property(property="description", type="string"),
     *       )
     *      )
     *     )
     *   )
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function get(Request $request): JsonResponse {

        $games = Game::all();
        $gamesList = [];
        foreach ($games as $game) {
            $inventory = Inventory::where('gameId', $game['id'])->get();
            $gamesList[] = [
                'game' => $game,
                'inventory' => $inventory
            ];
        }

        return $this->sendResponse($gamesList, 'games');
    }
}
