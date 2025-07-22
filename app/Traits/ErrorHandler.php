<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Exception;

trait ErrorHandler
{
    /**
     * Execute a callback with error handling
     */
    protected function executeWithErrorHandling(callable $callback, Request $request = null, $fallbackMessage = 'An error occurred while processing your request.')
    {
        try {
            return $callback();
        } catch (ModelNotFoundException $e) {
            Log::warning('Model not found', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'user_id' => auth()->id(),
                'url' => $request ? $request->fullUrl() : null,
            ]);
            return $this->errorResponse('The requested item was not found.', 404);
        } catch (ValidationException $e) {
            Log::warning('Validation failed', [
                'errors' => $e->errors(),
                'user_id' => auth()->id(),
                'url' => $request ? $request->fullUrl() : null,
            ]);
            if ($request && $request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed.',
                    'errors' => $e->errors()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (Exception $e) {
            Log::error('Controller error', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id(),
                'url' => $request ? $request->fullUrl() : null,
            ]);
            return $this->errorResponse($fallbackMessage, 500);
        }
    }

    /**
     * Handle database operations with error handling
     */
    protected function handleDatabaseOperation(callable $operation, Request $request = null, $successMessage = 'Operation completed successfully.')
    {
        return $this->executeWithErrorHandling(function () use ($operation, $successMessage) {
            $result = $operation();
            
            if ($request && $request->expectsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => $successMessage,
                    'data' => $result
                ]);
            }
            
            return redirect()->back()->with('success', $successMessage);
        }, $request, 'Database operation failed. Please try again.');
    }

    /**
     * Handle file uploads with error handling
     */
    protected function handleFileUpload($file, $destination, Request $request = null, $customFileName = null)
    {
        return $this->executeWithErrorHandling(function () use ($file, $destination, $customFileName) {
            if (!$file || !$file->isValid()) {
                throw new Exception('Invalid file uploaded.');
            }

            $fileName = $customFileName ?: time() . '_' . $file->getClientOriginalName();
            $file->storeAs($destination, $fileName);
            
            return $fileName;
        }, $request, 'File upload failed. Please try again.');
    }

    /**
     * Handle email sending with error handling
     */
    protected function handleEmailSending(callable $emailCallback, Request $request = null)
    {
        return $this->executeWithErrorHandling(function () use ($emailCallback) {
            return $emailCallback();
        }, $request, 'Email could not be sent. Please try again later.');
    }

    /**
     * Return error response based on request type
     */
    protected function errorResponse($message, $statusCode = 500, $request = null)
    {
        if ($request && $request->expectsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => $message
            ], $statusCode);
        }
        
        return redirect()->back()->with('error', $message);
    }

    /**
     * Return success response based on request type
     */
    protected function successResponse($message, $data = null, $request = null)
    {
        if ($request && $request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => $data
            ]);
        }
        
        return redirect()->back()->with('success', $message);
    }

    /**
     * Check permissions with error handling
     */
    protected function checkPermission($permission, $request = null)
    {
        if (!auth()->user()->can($permission)) {
            return $this->errorResponse('Permission denied.', 403, $request);
        }
        
        return true;
    }

    /**
     * Validate request with error handling
     */
    protected function validateRequest(Request $request, array $rules, array $messages = [])
    {
        return $this->executeWithErrorHandling(function () use ($request, $rules, $messages) {
            return $request->validate($rules, $messages);
        }, $request, 'Validation failed.');
    }
} 