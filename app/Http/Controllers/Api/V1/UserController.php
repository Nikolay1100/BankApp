<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;

class UserController extends Controller
{
    public function update(UpdateUserRequest $request, User $user)
    {
        if ($user->id !== $request->user()->id) {
            return response()->json(['error' => 'You can only update your own profile.'], 403);
        }

        $user->update($request->validated());

        return response()->json($user);
    }
}
