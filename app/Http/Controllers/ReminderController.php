<?php


namespace App\Http\Controllers;


use App\Messages\Messages;
use App\Models\Reminder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
use function Symfony\Component\Translation\t;

class ReminderController extends \App\Http\Controllers\API\Controller
{
    /**
     * @OA\Post(
     * path="/getReminders",
     * summary="Reminder",
     * description="Reminders by api_token",
     * operationId="reminder",
     * security={
     * {"Authorization": {}}},
     * tags={"Reminder"},
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
     *       @OA\Property(property="reminders", type="array",
     *       @OA\Items(type="object",
     *       @OA\Property(property="id", type="string", example=0),
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="time", type="string", example="HH:mm"),
     *       @OA\Property(property="date", type="string", example="Enum(Sunday, Monday, Tuesday, Wednesday, Thursday, Friday, Saturday) -> String or format(yyyy-MM-dd) -> String"),
     *       @OA\Property(property="repeat", type="boolean", example="false"),
     *       @OA\Property(property="color", type="string", example="Enum(Orange, Blue, LightBlue, Green, Purple, Yellow, Pink) -> String"),
     *       @OA\Property(property="type", type="string", example="Enum(Custom, Template) ->String"),
     *       @OA\Property(property="state", type="string", example="Enum(On, Off) -> String ->String"),
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

        if (!$this->getUserByToken($api_token)) {
            return $this->sendError(Messages::userError);
        }

        try {
            $reminder = Reminder::where('api_token', $api_token)->get();
        } catch (Exception $exception) {
            return $this->sendFailure($request, $exception, method: "/getReminders");
        }

        return $this->sendResponse($reminder, 'reminders');
    }

    /**
     * @OA\Post(
     * path="/addReminder",
     * summary="addReminder",
     * description="addReminder by api_token, name, note, time, date, repeat, color, type",
     * operationId="addReminder",
     * security={
     * {"Authorization": {}}},
     * tags={"Reminder"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"name", "note", "time", "date", "repeat", "color", "type"},
     *       @OA\Property(property="name", type="string", example="Example"),
     *       @OA\Property(property="note", type="string"),
     *       @OA\Property(property="time", type="string", example="HH:mm"),
     *       @OA\Property(property="date", type="string", example="05/01/2023"),
     *       @OA\Property(property="enums", type="array", @OA\Items(type="string")),
     *       @OA\Property(property="repeat", type="string", example="1 == true or 0 == false -> Отправляем строкой!"),
     *       @OA\Property(property="color", type="string", example="Enum(Orange, Blue, LightBlue, Green, Purple, Yellow, Pink, NotColor) -> String"),
     *       @OA\Property(property="type", type="string", example="Enum(Custom, Template) ->String"),
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
     *       @OA\Property(property="success", type="string", example="Reminder added successfully")
     *     )
     *   )
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function add(Request $request): JsonResponse
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'time' => 'required|string',
            'repeat' => 'required|string',
            'color' => 'required|string',
            'type' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError(Messages::allFieldsError);
        }

        $api_token = $this->getApiToken($request);

        $user = $this->getUserByToken($api_token);

        if (!$user) {
            return $this->sendError(Messages::userError);
        }

        if (!$request['date']) {
            $period = implode(",", $request['enums']);
        } else {
            $period = $request['date'];
        }

        $name = $request['name'];
        $note = $request['note'];
        $time = $request['time'];

        $repeat = false;
        if ($request['repeat'] == "1") {
            $repeat = true;
        }
        $color = $request['color'];
        $type = $request['type'];

        try {
            Reminder::forceCreate(
                [
                    'api_token' => $api_token,
                    'name' => $name,
                    'note' => $note,
                    'time' => $time,
                    'date' => $period,
                    'repeat' => $repeat,
                    'color' => $color,
                    'type' => $type,
                    'state' => 'On'
                ]);
        } catch (Exception $exception) {
            return $this->sendFailure($request, $exception, method: "/addReminder");
        }

        return $this->sendSuccess(Messages::reminderAddedSuccess);
    }

    /**
     * @OA\Post(
     * path="/deleteReminder",
     * summary="deleteReminder",
     * description="deleteReminder by api_token, id",
     * operationId="deleteReminder",
     * security={
     * {"Authorization": {}}},
     * tags={"Reminder"},
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
     *       @OA\Property(property="error", type="string", example="User does not exist")
     *    )
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *       @OA\Property(property="result", type="string", example="success"),
     *       @OA\Property(property="success", type="string", example="Reminder delete successfully")
     *     )
     *   )
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {

        $validator = Validator::make($request->all(), [
            'id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError(Messages::allFieldsError);
        }

        try {
            $user = $this->getUserByToken($this->getApiToken($request));

            if (!$user) {
                return $this->sendError(Messages::userError);
            }

            $reminder = Reminder::where('id', $request['id'])->first();

            $reminder->delete();

        } catch (Exception $exception) {
            return $this->sendFailure($request, $exception, method: "/deleteReminder");
        }

        return $this->sendSuccess(Messages::reminderDeleteSuccess);
    }

    /**
     * @OA\Post(
     * path="/editReminder",
     * summary="editReminder",
     * description="editReminder by id, api_token, name, note, time, date, repeat, color, type",
     * operationId="editReminder",
     * security={
     * {"Authorization": {}}},
     * tags={"Reminder"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"id", "name", "note", "time", "date", "repeat", "color", "type"},
     *       @OA\Property(property="id", type="string", example=0),
     *       @OA\Property(property="name", type="string", example="Example"),
     *       @OA\Property(property="note", type="string"),
     *       @OA\Property(property="time", type="string", example="HH:mm"),
     *       @OA\Property(property="date", type="string", example="Enum(Sunday, Monday, Tuesday, Wednesday, Thursday, Friday, Saturday) -> String or format(yyyy-MM-dd) -> String"),
     *       @OA\Property(property="repeat", type="boolean", example="true"),
     *       @OA\Property(property="color", type="string", example="Enum(Orange, Blue, LightBlue, Green, Purple, Yellow, Pink) -> String"),
     *       @OA\Property(property="type", type="string", example="Enum(Custom, Template) ->String"),
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
     *       @OA\Property(property="success", type="string", example="Reminder edited successfully")
     *     )
     *   )
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function edit(Request $request): JsonResponse
    {

        $validator = Validator::make($request->all(), [
            'id' => 'required|string',
            'name' => 'required|string',
            'note' => 'required|string',
            'time' => 'required|string',
            'date' => 'required|string',
            'repeat' => 'required|boolean',
            'color' => 'required|string',
            'type' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError(Messages::allFieldsError);
        }

        $api_token = substr($request->headers->get('Authorization', ''), 7);

        $user = $this->getUserByToken($api_token);

        if (!$user) {
            return $this->sendError(Messages::userError);
        }

        try {
            $reminder = Reminder::where('id', $request['id'])->first();

            if (!$reminder) {
                return $this->sendError(Messages::reminderEditError);
            }

            if (!$this->stringIsEmptyOrNull($request['name']) != $reminder['name']) {
                $reminder->name = $request['name'];
                $reminder->save();
            }

            if (!$this->stringIsEmptyOrNull($request['note']) && $request['note'] != $reminder['note']) {
                $reminder->note = $request['note'];
                $reminder->save();
            }

            if (!$this->stringIsEmptyOrNull($request['time']) && $request['time'] != $reminder['time']) {
                $reminder->time = $request['time'];
                $reminder->save();
            }

            if (!$this->stringIsEmptyOrNull($request['date']) && $request['date'] != $reminder['date']) {
                $reminder->date = $request['date'];
                $reminder->save();
            }

            if (!$this->stringIsEmptyOrNull($request['repeat']) && $request['repeat'] != $reminder['repeat']) {
                $reminder->repeat = $request['repeat'];
                $reminder->save();
            }

            if (!$this->stringIsEmptyOrNull($request['color']) && $request['color'] != $reminder['color']) {
                $reminder->color = $request['color'];
                $reminder->save();
            }

            if (!$this->stringIsEmptyOrNull($request['type']) && $request['type'] != $reminder['type']) {
                $reminder->type = $request['type'];
                $reminder->save();
            }
        } catch (Exception $exception) {
            return $this->sendFailure($request, $exception, method: "/editReminder");
        }

        return $this->sendSuccess(Messages::reminderEditSuccess);
    }
}
