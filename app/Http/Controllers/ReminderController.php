<?php


namespace App\Http\Controllers;


use App\Messages\Messages;
use App\Models\Reminder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReminderController extends \App\Http\Controllers\API\Controller
{
    /**
     * @OA\Post(
     * path="/getReminders",
     * summary="Reminder",
     * description="Reminders by api_token",
     * operationId="reminder",
     * tags={"Reminder"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Api Token",
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
    public function get(Request $request): JsonResponse {

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

        $reminder = Reminder::where('api_token', $api_token)->get();

        return $this->sendResponse($reminder, 'reminders');
    }

    /**
     * @OA\Post(
     * path="/addReminder",
     * summary="addReminder",
     * description="addReminder by api_token, name, note, time, date, repeat, color, type",
     * operationId="addReminder",
     * tags={"Reminder"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"api_token", "name", "note", "time", "date", "repeat", "color", "type"},
     *       @OA\Property(property="api_token", type="string", example="OzQ50ke3GElJMNvBZm8uksngp8dqNVYAHqr5CGHN9visYI0TYHg1fFdhsNf8BqTpwqDwXqcPhcxzN3Pj"),
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
     *       @OA\Property(property="success", type="string", example="Reminder added successfully")
     *     )
     *   )
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function add(Request $request): JsonResponse {

        $validator = Validator::make($request->all(), [
            'api_token' => 'required|string',
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

        $api_token = $request['api_token'];

        $user = $this->getUserByToken($api_token);

        if (!$user) {
            return $this->sendError(Messages::userError);
        }

        $name = $request['name'];
        $note = $request['note'];
        $time = $request['time'];
        $date = $request['date'];
        $repeat = $request['repeat'];
        $color = $request['color'];
        $type = $request['type'];

        Reminder::forceCreate(
            [
                'api_token' => $api_token,
                'name' => $name,
                'note' => $note,
                'time' => $time,
                'date' => $date,
                'repeat' => $repeat,
                'color' => $color,
                'type' => $type,
                'state' => 'On'
            ]);

        return $this->sendSuccess(Messages::reminderAddedSuccess);
    }

    /**
     * @OA\Post(
     * path="/deleteReminder",
     * summary="deleteReminder",
     * description="deleteReminder by api_token, id",
     * operationId="deleteReminder",
     * tags={"Reminder"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"api_token", "id"},
     *       @OA\Property(property="api_token", type="string", example="OzQ50ke3GElJMNvBZm8uksngp8dqNVYAHqr5CGHN9visYI0TYHg1fFdhsNf8BqTpwqDwXqcPhcxzN3Pj"),
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
    public function delete(Request $request): JsonResponse {

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

        $id = $request['id'];

        $reminder = Reminder::where('id', $id)->first();
        $reminder->take(1)->delete();

        return $this->sendSuccess(Messages::reminderDeleteSuccess);
    }

    /**
     * @OA\Post(
     * path="/editReminder",
     * summary="editReminder",
     * description="editReminder by id, api_token, name, note, time, date, repeat, color, type",
     * operationId="editReminder",
     * tags={"Reminder"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"id", "api_token", "name", "note", "time", "date", "repeat", "color", "type"},
     *       @OA\Property(property="id", type="string", example=0),
     *       @OA\Property(property="api_token", type="string", example="OzQ50ke3GElJMNvBZm8uksngp8dqNVYAHqr5CGHN9visYI0TYHg1fFdhsNf8BqTpwqDwXqcPhcxzN3Pj"),
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
    public function edit(Request $request): JsonResponse {

        $validator = Validator::make($request->all(), [
            'id' => 'required|string',
            'api_token' => 'required|string',
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

        $api_token = $request['api_token'];

        $user = $this->getUserByToken($api_token);

        if (!$user) {
            return $this->sendError(Messages::userError);
        }

        $requestId = $request['id'];

        $reminder = Reminder::where('id', $requestId)->first();

        if (!$reminder) {
            return $this->sendError(Messages::reminderEditError);
        }

        $requestName = $request['name'];
        $requestNote = $request['note'];
        $requestTime = $request['time'];
        $requestDate = $request['date'];
        $requestRepeat = $request['repeat'];
        $requestColor = $request['color'];
        $requestType = $request['type'];

        $reminderName = $reminder['name'];
        $reminderNote = $reminder['note'];
        $reminderTime= $reminder['time'];
        $reminderDate = $reminder['date'];
        $reminderRepeat = $reminder['repeat'];
        $reminderColor = $reminder['color'];
        $reminderType = $reminder['type'];

        if ($requestName != $reminderName) {
            $reminder->name = $requestName;
            $reminder->save();
        }

        if ($requestNote != $reminderNote) {
            $reminder->note = $requestNote;
            $reminder->save();
        }

        if ($requestTime != $reminderTime) {
            $reminder->time = $requestTime;
            $reminder->save();
        }

        if ($requestDate != $reminderDate) {
            $reminder->date = $requestDate;
            $reminder->save();
        }

        if ($requestRepeat != $reminderRepeat) {
            $reminder->repeat = $requestRepeat;
            $reminder->save();
        }

        if ($requestColor != $reminderColor) {
            $reminder->color = $requestColor;
            $reminder->save();
        }

        if ($requestType != $reminderType) {
            $reminder->type = $requestType;
            $reminder->save();
        }

        return $this->sendSuccess(Messages::reminderEditSuccess);
    }
}
