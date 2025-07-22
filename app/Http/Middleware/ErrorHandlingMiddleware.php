<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Exception;

class ErrorHandlingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            return $next($request);
        } catch (ModelNotFoundException $e) {
            Log::warning('Model not found in middleware', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'user_id' => auth()->id(),
                'url' => $request->fullUrl(),
            ]);
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The requested resource was not found.'
                ], 404);
            }
            return redirect()->back()->with('error', 'The requested item was not found.');
        } catch (ValidationException $e) {
            Log::warning('Validation failed in middleware', [
                'errors' => $e->errors(),
                'user_id' => auth()->id(),
                'url' => $request->fullUrl(),
            ]);
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed.',
                    'errors' => $e->errors()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (NotFoundHttpException $e) {
            Log::warning('Page not found in middleware', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'user_id' => auth()->id(),
                'url' => $request->fullUrl(),
            ]);
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The requested page was not found.'
                ], 404);
            }
            return redirect()->back()->with('error', 'The requested page was not found.');
        } catch (MethodNotAllowedHttpException $e) {
            Log::warning('Method not allowed in middleware', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'user_id' => auth()->id(),
                'url' => $request->fullUrl(),
            ]);
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Method not allowed.'
                ], 405);
            }
            return redirect()->back()->with('error', 'Method not allowed.');
        } catch (Exception $e) {
            Log::error('Unexpected error in middleware', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id(),
                'url' => $request->fullUrl(),
            ]);
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'An unexpected error occurred. Please try again.'
                ], 500);
            }
            return redirect()->back()->with('error', 'An unexpected error occurred. Please try again.');
        }
    }
} 