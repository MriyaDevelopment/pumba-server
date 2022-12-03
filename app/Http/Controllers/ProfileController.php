<?php


namespace App\Http\Controllers;
use App\Messages\Messages;
use App\Models\Child;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class ProfileController extends Controller
{

    public function profile(Request $request): JsonResponse {

        $profile = $this->getUserByToken($request['api_token']);

        if (!$profile) {
            return $this->sendError(Messages::profileError);
        }

        return $this->sendResponse($profile, 'profile');
    }

    public function edit(Request $request): JsonResponse {

        $api_token = $request['api_token'];

        $profile = $this->getUserByToken($api_token);

        if (!$profile) {
            return $this->sendError(Messages::profileError);
        }

        $profileName = $profile['name'];
        $profileAvatar = $profile['avatar'] ?: "profileNull";
        $profileRole = $profile['role'];

        $requestName = $request['name'];
        $requestAvatar = $request['avatar'] ?: "requestNull";
        $requestRole = $request['role'];

        if ($profileName != $requestName) {
            $profile->name = $requestName;
            $profile->save();
        }

        if ($profileRole != $requestRole) {
            $profile->role = $requestRole;
            $profile->save();
        }

        if ($profileAvatar != $requestAvatar) {
            $profile->avatar = $this->uploadImage($requestAvatar);
            $profile->save();
        }

        return $this->sendSuccess(Messages::profileEditedSuccess);
    }

    public function delete(Request $request): JsonResponse {

        $api_token = $request['api_token'];

        $profile = $this->getUserByToken($api_token);

        if (!$profile) {
            return $this->sendError(Messages::profileError);
        }

        $profile->take(1)->delete();

        $children = $this->getChildrenByToken($api_token);

        foreach ($children as $child) {
            Child::where('id', $child['id'])->take(1)->delete();
        }

        return $this->sendSuccess(Messages::profileDeleteSuccess);
    }


}
