<?php

namespace App\Services;

use App\Exceptions\ServiceException;
use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthService
{
    private const REFRESH_TOKEN_TTL_DAYS = 30;

    public function register(array $data): array
    {
        try {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $accessToken  = auth('api')->login($user);
            $refreshToken = $this->generateRefreshToken($user);

            return $this->tokenResponse($accessToken, $refreshToken, $user);
        } catch (\Throwable $e) {
            Log::error('AuthService::register failed', [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'email'     => $data['email'] ?? null,
            ]);

            throw new ServiceException(__('api.errors.server'));
        }
    }

    public function login(array $credentials): array|false
    {
        $token = auth('api')->attempt([
            'email'    => $credentials['email'],
            'password' => $credentials['password'],
        ]);

        if (! $token) {
            return false;
        }

        /** @var User $user */
        $user = auth('api')->user();

        if (! $user->is_active) {
            auth('api')->logout();
            return false;
        }

        $user->update(['last_login_at' => now()]);

        $refreshToken = $this->generateRefreshToken($user);

        return $this->tokenResponse($token, $refreshToken, $user);
    }

    public function refresh(string $plainToken): array
    {
        $hashed = hash('sha256', $plainToken);

        $record = RefreshToken::where('token', $hashed)
            ->where('expires_at', '>', now())
            ->with('user')
            ->first();

        if (! $record) {
            throw new ServiceException(__('api.errors.token_invalid'), 401);
        }

        $user = $record->user;

        if (! $user->is_active) {
            $record->delete();
            throw new ServiceException(__('api.errors.unauthorized'), 401);
        }

        try {
            // Rotate refresh token
            $record->delete();
            $newRefreshToken = $this->generateRefreshToken($user);

            $accessToken = auth('api')->login($user);

            return $this->tokenResponse($accessToken, $newRefreshToken, $user);
        } catch (ServiceException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('AuthService::refresh failed', [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'user_id'   => $user->id,
            ]);

            throw new ServiceException(__('api.errors.server'));
        }
    }

    public function logout(string $plainToken): void
    {
        try {
            $hashed = hash('sha256', $plainToken);
            RefreshToken::where('token', $hashed)->delete();
            auth('api')->logout();
        } catch (\Throwable $e) {
            Log::error('AuthService::logout failed', [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
            ]);

            throw new ServiceException(__('api.errors.server'));
        }
    }

    public function me(): User
    {
        return auth('api')->user();
    }

    private function generateRefreshToken(User $user): string
    {
        $plain = Str::random(64);

        RefreshToken::create([
            'user_id'    => $user->id,
            'token'      => hash('sha256', $plain),
            'expires_at' => now()->addDays(self::REFRESH_TOKEN_TTL_DAYS),
        ]);

        return $plain;
    }

    private function tokenResponse(string $accessToken, string $refreshToken, User $user): array
    {
        return [
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type'    => 'bearer',
            'expires_in'    => auth('api')->factory()->getTTL() * 60,
            'user'          => $user,
        ];
    }
}
