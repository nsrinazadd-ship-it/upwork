<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFreelancerIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user=$request->user();
        if($user && $user->freelancerProfile && !$user->freelancerProfile->is_verified){
            return response()->json([
                'status'=>'error',
                'error_code'=>'PROFILE_UNVERIFIED',
                'message' => 'يجب إكمال توثيق حسابك أولاً للقيام بهذا الإجراء.',
            ],Response::HTTP_FORBIDDEN);
        }
        return $next($request);
    }
}
