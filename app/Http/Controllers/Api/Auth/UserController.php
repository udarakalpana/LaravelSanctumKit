<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use ApiResponse;

    public function show(Request $request)
    {
        return $this->successResponse(
            ['user' => new UserResource($request->user())],
            'User profile retrieved'
        );
    }
}
