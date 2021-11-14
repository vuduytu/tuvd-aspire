<?php

namespace App\Exceptions;

use App\Traits\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponse;
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof ValidationException) {
            $errors = $exception->validator->errors()->getMessages();
            return $this->sendValidationFail($exception->getMessage(), $errors);
        }

        if ($exception instanceof MethodNotAllowedHttpException) {
            return $this->sendError('The specified method for the request is invalid', METHOD_INVALID_STATUS, METHOD_INVALID_STATUS);
        }

        if ($exception instanceof NotFoundHttpException) {
            return $this->sendError('The specified URL cannot be found', NOT_FOUND_STATUS, NOT_FOUND_STATUS);
        }

        if ($exception instanceof ModelNotFoundException) {
            return $this->sendError('Not found record', NOT_FOUND_STATUS, NOT_FOUND_STATUS);
        }

        if ($exception instanceof HttpException) {
            return $this->sendError($exception->getMessage(), $exception->getStatusCode(), $exception->getStatusCode());
        }

        if ($exception instanceof QueryException) {
            $errorCode = $exception->errorInfo[1];

            if ($errorCode == 1451) {
                return $this->sendError('Cannot remove this resource permanently. It is related with any other resource', CANNOT_REMOVE_RESOURCE_STATUS, CANNOT_REMOVE_RESOURCE_STATUS);
            }
        }

        if ($exception instanceof TokenBlacklistedException || $exception instanceof TokenExpiredException || $exception instanceof TokenInvalidException) {
            return response()->json([
                'success' => false,
                'message' => 'Đã hết hạn token ! Vui lòng đăng nhập lại!',
                'code' => 401
            ], 401);
        }

        return $this->sendError($exception->getMessage());
    }
}
