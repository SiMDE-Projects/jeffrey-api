<?php

namespace App\Http\Controllers;

use App\Http\Middleware\RefreshJWTToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware(RefreshJWTToken::class, ['only' => 'refresh']);
        $this->middleware('auth', ['only' => 'me']);
    }

    /**
     * Logs an user in
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        // Validate request
        $this->validate($request, [
            'username' => ['required', 'string'],
            'password' => ['required', 'string']
        ]);

        $credentials = $request->only(['username', 'password']);

        if (! $token = Auth::attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Generates a new JWT token
     */
    public function refresh()
    {
        return response(null, 200);
    }

    /**
     * Shows the current user data
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function me()
    {
        return Auth::user();
    }

    /**
     * Send a JSON response with the user model and its JWT token
     * @param $token
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'user' => Auth::user(),
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60
        ], 200);
    }
}
