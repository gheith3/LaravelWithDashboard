<?php

use App\Exceptions\ServiceException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->api(prepend: [
            \App\Http\Middleware\ForceJsonResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*')
        );

        // ── JWT exceptions ────────────────────────────────────────────────
        $exceptions->render(function (TokenExpiredException $e, Request $request) {
            if ($request->is('api/*')) {
                Log::warning(__('api.errors.token_expired'), apiErrorContext($request));
                return response()->json(['success' => false, 'message' => __('api.errors.token_expired')], 401);
            }
        });

        $exceptions->render(function (TokenInvalidException $e, Request $request) {
            if ($request->is('api/*')) {
                Log::warning(__('api.errors.token_invalid'), apiErrorContext($request));
                return response()->json(['success' => false, 'message' => __('api.errors.token_invalid')], 401);
            }
        });

        $exceptions->render(function (JWTException $e, Request $request) {
            if ($request->is('api/*')) {
                Log::warning($e->getMessage(), apiErrorContext($request));
                return response()->json(['success' => false, 'message' => __('api.errors.token_invalid')], 401);
            }
        });

        // ── Auth exceptions ───────────────────────────────────────────────
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                Log::warning(__('api.errors.unauthorized'), apiErrorContext($request));
                return response()->json(['success' => false, 'message' => __('api.errors.unauthorized')], 401);
            }
        });

        // ── Model not found ───────────────────────────────────────────────
        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->is('api/*')) {
                Log::warning(__('api.errors.not_found'), [...apiErrorContext($request), 'model' => $e->getModel()]);
                return response()->json(['success' => false, 'message' => __('api.errors.not_found')], 404);
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                Log::warning(__('api.errors.not_found'), apiErrorContext($request));
                return response()->json(['success' => false, 'message' => __('api.errors.not_found')], 404);
            }
        });

        // ── Validation exceptions ─────────────────────────────────────────
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                Log::warning(__('api.errors.validation'), [...apiErrorContext($request), 'errors' => $e->errors()]);
                return response()->json([
                    'success' => false,
                    'message' => __('api.errors.validation'),
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        // ── Service exceptions (already logged in service layer) ──────────
        $exceptions->render(function (ServiceException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], $e->statusCode);
            }
        });

        // ── Unhandled exceptions (500) ────────────────────────────────────
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                Log::error($e->getMessage(), [
                    ...apiErrorContext($request),
                    'exception' => get_class($e),
                    'file'      => $e->getFile(),
                    'line'      => $e->getLine(),
                    'trace'     => $e->getTraceAsString(),
                ]);
                return response()->json(['success' => false, 'message' => __('api.errors.server')], 500);
            }
        });
    })->create();
