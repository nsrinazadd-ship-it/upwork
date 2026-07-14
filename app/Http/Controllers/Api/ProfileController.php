<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Resources\ProfileResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    use ApiResponse;

    public function show():JsonResponse{
        $user=Auth::user();

        return $this->success(new ProfileResource($user),'Profile data retrieved successfully.');
    }

    public function update(UpdateProfileRequest $request):JsonResponse{
        /** @var \App\Models\User $user */
        $user=Auth::user();
        $user->update($request->validated());
        return $this->success(new ProfileResource($user),'Profile updated successfully.');
    }
}
