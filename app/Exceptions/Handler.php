<?php

namespace App\Exceptions;

    use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {

        if (!strpos($request->getUri(), '/api')) {
            return parent::render($request, $e);
        }

        switch (true) {
            case $e instanceof ValidationException:
                $errors = $this->convertError($e->errors());
                return $this->withErrorValidation($e->getMessage(), $errors);
            case $e instanceof AuthenticationException:
                $statusCode = Response::HTTP_UNAUTHORIZED;
                $errors = 'UNAUTHORIZED';
                break;
            case $e instanceof ModelNotFoundException:
                $statusCode = Response::HTTP_NOT_FOUND;
                $errors = 'NOT_FOUND';
                break;
            case $e instanceof MethodNotAllowedHttpException:
                $statusCode = Response::HTTP_METHOD_NOT_ALLOWED;
                $errors = 'METHOD_NOT_ALLOWED';
                break;
            case $e instanceof BadRequestException:
                $statusCode = Response::HTTP_BAD_REQUEST;
                $errors = 'BAD_REQUEST';
                break;
            default:
                $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
                $errors = $e->getMessage() . 'File ' . $e->getFile() . 'Line: ' . $e->getLine();
                Log::debug($errors);
        }

        return $this->error($errors, $statusCode);
    }

    public function withErrorValidation($message, $errors): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    protected function convertError($errorArr): array
    {
        $errors = $errorArr ?: [];
        $firstError = array_shift($errors)[0] ?? null;
        $firstError = $this->getErrorName($firstError);
        $errorData = config("error.$firstError");

        if (! $errorData) {
            $errorData = [
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => $firstError,
            ];
        }

        return $errorData;
    }

    protected function error($message, int $code = Response::HTTP_INTERNAL_SERVER_ERROR): JsonResponse
    {
        return response()->json(
            [
                'success'  => false,
                'data'    => [],
                'errors'   => [
                    'code' => $code,
                    'message' => $message
                ],
            ],
            $code
        );
    }

    /**
     * @param $txt
     *
     * @return string
     */
    public function getErrorName($txt): string
    {
        $txt = str_replace('.', '_', $txt);

        return trim($txt, '_');
    }
}
