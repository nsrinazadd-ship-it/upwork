<?php

namespace App\Http\Middleware;

use App\Models\ApiRequestLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request , Response $response):void{
        $duration=microtime(true)-LARAVEL_START;

        if($request->is('telescope*')){
            return;
        }

        ApiRequestLog::create([
            'user_id'     => $request->user()?->id,
            'method'      => $request->method(),
            'url'         => $request->fullUrl(),
            'status_code' => $response->getStatusCode(),
            'duration'    => $duration,
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
            // نسجل الـ Payload فقط في العمليات التي تعدل البيانات تفادياً لضخامة الحجم
            'payload'     => $request->isMethodSafe() ? null : json_encode($request->except(['password', 'password_confirmation'])),
        ]);
    }
}
