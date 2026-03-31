<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Responses\V1\User\UpdateUserResponse;

class UserController extends Controller
{
    /**
     * Updates the user's profile.
     */
    public function update(UpdateUserRequest $request, User $user): UpdateUserResponse
    {
        $user->update($request->validated());

        return new UpdateUserResponse($user);
    }
}
