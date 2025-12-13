<?php

namespace App\Http\Middleware;

use App\Models\NexptgApiUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class NexptgBasicAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authorization = $request->header('Authorization');

        if (! $authorization || ! str_starts_with($authorization, 'Basic ')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $base64Credentials = substr($authorization, 6);
        $credentials = base64_decode($base64Credentials, true);

        if ($credentials === false) {
            return response()->json(['error' => 'Invalid authorization header'], 401);
        }

        [$username, $password] = explode(':', $credentials, 2) + [null, null];

        if (! $username || ! $password) {
            return response()->json(['error' => 'Invalid credentials format'], 401);
        }

        $apiUser = NexptgApiUser::where('username', $username)
            ->where('is_active', true)
            ->first();

        if (! $apiUser || ! Hash::check($password, $apiUser->password)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $apiUser->updateLastUsedAt();

        // Attach API user to request for use in controller
        $request->merge(['nexptg_api_user' => $apiUser]);

        return $next($request);
    }
}
