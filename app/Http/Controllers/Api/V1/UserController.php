<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function update(\App\Http\Requests\UpdateUserRequest $request, \App\Models\User $user)
    {
        $user->update($request->validated());

        return response()->json($user);
    }
}
