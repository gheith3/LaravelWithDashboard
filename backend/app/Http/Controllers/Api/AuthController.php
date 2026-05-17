<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RefreshRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Resources\Api\UserResource;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly AuthService $authService) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return $this->success(
            $this->formatTokenData($result),
            __('api.auth.register_success'),
            201
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        if (! $result) {
            return $this->error(__('api.auth.invalid_credentials'), 401);
        }

        return $this->success(
            $this->formatTokenData($result),
            __('api.auth.login_success')
        );
    }

    public function refresh(RefreshRequest $request): JsonResponse
    {
        $result = $this->authService->refresh($request->validated('refresh_token'));

        return $this->success(
            $this->formatTokenData($result),
            __('api.auth.token_refreshed')
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->input('refresh_token', ''));

        return $this->success(message: __('api.auth.logout_success'));
    }

    public function me(): JsonResponse
    {
        return $this->success(
            new UserResource($this->authService->me()),
            __('api.auth.profile_retrieved')
        );
    }

    private function formatTokenData(array $result): array
    {
        return [
            'access_token'  => $result['access_token'],
            'refresh_token' => $result['refresh_token'],
            'token_type'    => $result['token_type'],
            'expires_in'    => $result['expires_in'],
            'user'          => new UserResource($result['user']),
        ];
    }
}
