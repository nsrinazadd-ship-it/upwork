<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Requests\Api\LoginRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
class AuthController extends Controller
{
    use ApiResponse;

    public function register(RegisterRequest $request):JsonResponse{
        $validated =$request->validated();

        $user=User::create([
            'first_name' =>$validated['first_name'],
            'last_name'  =>$validated['last_name'],
            'email'=>$validated['email'],
            'password'=>Hash::make($validated['password']),
            'role' =>$validated['role'],
        ]);

        $token=$user->createToken($request->input('device_name', 'web_app'))->plainTextToken;

        return $this->success([
            'user'         => $user,
            'access_token' => $token,
            'token_type'   => 'Bearer'
        ], 'User registered successfully.');
    }

    public function login(LoginRequest $request):JsonResponse{

        $validated=$request->validated();

        $user=User::where('email',$validated['email'])->first();

        if(! $user || ! Hash::check($validated['password'],$user->password)){
            return $this->error('Invalid credentials provided.', 401);
        }

        $token = $user->createToken($request->input('device_name', 'web_app'))->plainTextToken;

        return $this->success([
            'user'         => $user,
            'access_token' => $token,
            'token_type'   => 'Bearer'
        ], 'Authenticated successfully.'
        );
    }

    public function logout():JsonResponse{
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->currentAccessToken()->delete();
        return $this->success(null, 'Logged out successfully and token revoked.');
    }
}
