<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

trait ApiResponse
{
    protected function success(mixed $data = null, string $message = 'Success', int $status = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }

    protected function paginate(mixed $data, LengthAwarePaginator $paginator, string $message = 'Success'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
            ],
        ]);
    }

    protected function error(string $message, int $status = 400, mixed $errors = null): JsonResponse
    {
        $this->logApiError($message, $status, $errors);

        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    private function logApiError(string $message, int $status, mixed $errors): void
    {
        $context = [
            'status'  => $status,
            'url'     => request()->fullUrl(),
            'method'  => request()->method(),
            'user_id' => auth('api')->id(),
            'ip'      => request()->ip(),
        ];

        if ($errors !== null) {
            $context['errors'] = $errors;
        }

        if ($status >= 500) {
            Log::error($message, $context);
        } else {
            Log::warning($message, $context);
        }
    }
}
