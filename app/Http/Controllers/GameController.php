<?php

namespace App\Http\Controllers;

use App\Messages\Messages;
use App\Models\Game;
use App\Models\Guide;
use App\Models\Inventory;
use App\Models\SaveGame;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class GameController extends \App\Http\Controllers\API\Controller
{
    /**
     * @OA\Post(
     * path="/getGames",
     * summary="Games",
     * description="Games by api_token",
     * operationId="games",
     * tags={"Games"},
     * security={
     * {"Authorization": {}}},
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
     *       @OA\Property(property="game", type="object",
     *       @OA\Property(property="id", type="string"),
     *       @OA\Property(property="title", type="string"),
     *       @OA\Property(property="subtitle", type="string"),
     *       @OA\Property(property="type", type="string"),
     *       @OA\Property(property="time", type="string"),
     *       @OA\Property(property="image", type="string"),
     *       @OA\Property(property="description", type="string"),
     *       @OA\Property(property="inventory", type="array",
     *       @OA\Items(type="object",
     *       @OA\Property(property="id", type="string"),
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="image", type="string"),
     *       @OA\Property(property="gameId", type="string"),
     *       ),
     *       ),
     *       @OA\Property(property="isSaved", type="boolean")
     *       )
     *       )
     *      )
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
            return $this->sendError(Messages::profileError);
        }

        $door_type = $user['door_type'];
        $ages = $user['ages'];
        $time = $user['time'];
        $energy_level = $user['energy_level'];
        $stuff = $user['stuff'];

        $games = Game::all();
        $gamesList = [];

        foreach ($games as $game) {
            $isAge = strpos($game['ages'], $ages);
            $isTime = strpos($game['time'], $time);
            $isDoor = str_contains($game['door_type'], $door_type);
            $isEnergyLevel = str_contains($game['energy_level'], $energy_level);
            $isStuff = str_contains($game['stuff'], $stuff);

            if (($isAge !== false) && ($isTime !== false) && $isDoor && $isEnergyLevel && $isStuff) {
                $inventory = Inventory::where('gameId', $game['id'])->get();
                $savedGame = SaveGame::where('api_token', $api_token)->where('gameId', $game['id'])->first();
                $isSaved = false;
                if ($savedGame) {
                    $isSaved = true;
                }
                $game['inventory'] = $inventory;
                $game['isSaved'] = $isSaved;
                $gamesList[] = [
                    'game' => $game,
                ];
            }
        }

        return $this->sendResponse($gamesList, 'games');
    }

    /**
     * @OA\Post(
     * path="/getGameById",
     * summary="getGameById",
     * description="Games by api_token, id",
     * operationId="getGameById",
     * tags={"Games"},
     * security={
     * {"Authorization": {}}},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"id"},
     *       @OA\Property(property="id", type="string", example="4")
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
     *       @OA\Property(property="game", type="object",
     *       @OA\Property(property="id", type="string"),
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="time", type="string"),
     *       @OA\Property(property="energy_level", type="string"),
     *       @OA\Property(property="stuff", type="string"),
     *       @OA\Property(property="ages", type="string"),
     *       @OA\Property(property="description", type="string"),
     *       @OA\Property(property="inventory", type="array",
     *       @OA\Items(type="object",
     *       @OA\Property(property="id", type="string"),
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="image", type="string"),
     *       @OA\Property(property="gameId", type="string"),
     *       ),
     *      ),
     *      @OA\Property(property="isSaved", type="boolean"),
     *      )
     *     )
     *   )
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function getById(Request $request): JsonResponse
    {
        $api_token = substr($request->headers->get('Authorization', ''), 7);

        $user = $this->getUserByToken($api_token);
        if (!$user) {
            return $this->sendError(Messages::profileError);
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError(Messages::allFieldsError);
        }

        $game = Game::where('id', $request['id'])->first();

        if (!$game) {
            return $this->sendError(Messages::gameError);
        }
        $inventory = Inventory::where('gameId', $game['id'])->get();
        $savedGame = SaveGame::where('api_token', $api_token)->where('gameId', $game['id'])->first();

        $isSaved = false;

        if ($savedGame) {
            $isSaved = true;
        }

        $game['inventory'] = $inventory;
        $game['isSaved'] = $isSaved;

        return $this->sendResponse($game, 'game');
    }

    /**
     * @OA\Post(
     * path="/saveGame",
     * summary="Saved Games",
     * description="Saved Games by api_token, gameId",
     * operationId="saved games",
     * tags={"Games"},
     * security={
     * {"Authorization": {}}},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"gameId"},
     *       @OA\Property(property="gameId", type="string", example="123"),
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
     *       @OA\Property(property="saveInfo", type="object", example= {"isSaved" : false, "gameId" : "22" })
     *     )
     *   )
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function save(Request $request): JsonResponse
    {
        $api_token = substr($request->headers->get('Authorization', ''), 7);

        if (!$this->getUserByToken($api_token)) {
            return $this->sendError(Messages::profileError);
        }

        $validator = Validator::make($request->all(), [
            'gameId' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError(Messages::allFieldsError);
        }

        $savedGame = SaveGame::where('api_token', $api_token)->where('gameId', $request['gameId'])->first();
        $isSaved = false;
        if ($savedGame) {
            $savedGame->delete();
        } else {
            SaveGame::forceCreate([
                'api_token' => $api_token,
                'gameId' => $request['gameId'],
            ]);
            $isSaved = true;
        }
        $saveInfo = [
            'isSaved' => $isSaved,
            'gameId' => $request['gameId']
        ];
        return $this->sendResponse($saveInfo, 'saveInfo');
    }

    /**
     * @OA\Post(
     * path="/getSavedGames",
     * summary="getSavedGames",
     * description="getSavedGames by api_token",
     * operationId="getSavedGames",
     * tags={"Games"},
     * security={
     * {"Authorization": {}}},
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
     *       @OA\Property(property="game", type="object",
     *       @OA\Property(property="id", type="string"),
     *       @OA\Property(property="title", type="string"),
     *       @OA\Property(property="subtitle", type="string"),
     *       @OA\Property(property="type", type="string"),
     *       @OA\Property(property="time", type="string"),
     *       @OA\Property(property="image", type="string"),
     *       @OA\Property(property="description", type="string"),
     *       @OA\Property(property="inventory", type="array",
     *       @OA\Items(type="object",
     *       @OA\Property(property="id", type="string"),
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="image", type="string"),
     *       @OA\Property(property="gameId", type="string"),
     *       ),
     *       ),
     *       @OA\Property(property="isSaved", type="boolean")
     *       )
     *       )
     *      )
     *      )
     *     )
     *   )
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function getSavedGames(Request $request): JsonResponse
    {
        $api_token = substr($request->headers->get('Authorization', ''), 7);

        if (!$this->getUserByToken($api_token)) {
            return $this->sendError(Messages::profileError);
        }

        $games = Game::all();
        $gamesList = [];

        foreach ($games as $game) {
            $inventory = Inventory::where('gameId', $game['id'])->get();
            $savedGame = SaveGame::where('api_token', $api_token)->where('gameId', $game['id'])->first();
            $isSaved = false;
            if ($savedGame) {
                $isSaved = true;
            }
            $game['inventory'] = $inventory;
            $game['isSaved'] = $isSaved;
            if ($isSaved) {
                $gamesList[] = [
                    'game' => $game,
                ];
            }
        }

        return $this->sendResponse($gamesList, 'games');
    }
}
