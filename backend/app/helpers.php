<?php

use Illuminate\Http\Request;

if (! function_exists('apiErrorContext')) {
    function apiErrorContext(Request $request): array
    {
        return [
            'url'     => $request->fullUrl(),
            'method'  => $request->method(),
            'user_id' => auth('api')->id(),
            'ip'      => $request->ip(),
        ];
    }
}
