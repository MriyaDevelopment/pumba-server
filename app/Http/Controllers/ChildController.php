<?php


namespace App\Http\Controllers;

use App\Messages\Messages;
use App\Models\Child;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ChildController extends Controller
{
    public function children(Request $request): JsonResponse
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
            $child->avatar = $this->uploadImage($requestAvatar);
            $child->save();
        }

        return $this->sendSuccess(Messages::childEditedSuccess);
    }

    private function delete(Request $request): JsonResponse
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

        $child->take(1)->delete();

        return $this->sendSuccess(Messages::childDeleteSuccess);
    }

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

        if ($requestAvatar != "requestNull") {
            $requestAvatar = $this->uploadImage($requestAvatar);
        } else {
            $requestAvatar = null;
        }

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
