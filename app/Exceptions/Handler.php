<?php

namespace App\Exceptions;

use App\Traits\ApiResponseTrait;
use Error;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponseTrait;
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e): \Illuminate\Http\Response|JsonResponse|Response|RedirectResponse
    {
        if ($request->expectsJson() || Str::contains($request->path(), 'api')) {

            Log::error($e);

            if ($e instanceof AuthenticationException) {
                $statusCode = Response::HTTP_UNAUTHORIZED;
                return $this->apiResponse([
                    'message' => "Unauthenticated or Token expired, please try to login again",
                    'success' => false,
                    'exception' => $e,
                    'error_code' => $statusCode,
                ], $statusCode);
            }

            if ($e instanceof NotFoundHttpException) {
                return $this->apiResponse([
                    'message' => $e->getMessage(),
                    'success' => false,
                    'exception' => $e,
                    'error_code' => $e->getStatusCode(),
                ], $e->getStatusCode());
            }

            if ($e instanceof ValidationException) {
                $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY;
                return $this->apiResponse([
                    'message' => "Validation failed",
                    'success' => false,
                    'exception' => $e,
                    'error_code' => $statusCode,
                    'errors' => $e->errors(),
                ], $statusCode);
            }

            if ($e instanceof ModelNotFoundException) {
                $statusCode = Response::HTTP_NOT_FOUND;
                return $this->apiResponse([
                    'message' => "Resource could not be found",
                    'success' => false,
                    'exception' => $e,
                    'error_code' => $statusCode,
                ], $statusCode);
            }

            if ($e instanceof UniqueConstraintViolationException) {
                $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
                return $this->apiResponse([
                    'message' => "Duplicate entry found",
                    'success' => false,
                    'exception' => $e,
                    'error_code' => $statusCode,
                ]);
            }

            if ($e instanceof QueryException) {
                $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
                return $this->apiResponse([
                    'message' => "Could not execute query",
                    'success' => false,
                    'exception' => $e,
                    'error_code' => $statusCode,
                ]);
            }

//            if ($e instanceof MethodNotAllowedHttpException) {
//                return $this->apiResponse([
//                    'message' => $e->getMessage(),
//                    'success' => false,
//                    'exception' => $e,
//                    'error_code' => Response::HTTP_METHOD_NOT_ALLOWED,
//                ], Response::HTTP_METHOD_NOT_ALLOWED);
//            }

            if ($e instanceof Exception) {
                $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
                return $this->apiResponse([
                    'message' => "We could not handle your request, please try again later",
                    'success' => false,
                    'exception' => $e,
                    'error_code' => $statusCode,
                ]);
            }

            if ($e instanceof Error) {
                $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
                return $this->apiResponse([
                    'message' => "We could not handle your request, please try again later",
                    'success' => false,
                    'exception' => $e,
                    'error_code' => $statusCode,
                ]);
            }
        }
        return parent::render($request, $e);
    }
}
