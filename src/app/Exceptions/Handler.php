<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
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
     * Render an exception into an HTTP response or Json response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
     public function render($request, Throwable $exception)
     {
         if (!empty($exception)) {
             $response = [
                 'error' => 'Internal Server Error.'
             ];

             if (config('app.debug')) {
                 $response['exception'] = get_class($exception);
                 $response['message'] = $exception->getMessage();
                 $response['trace'] = $exception->getTrace();
             }

             if($exception instanceof ValidationException){
                 return $this->convertValidationExceptionToResponse($exception, $request);
             }else if($exception instanceof AuthenticationException){
                 $status = 401;

                 $response['error'] = 'Unauthenticated!';
             }else if($exception instanceof \PDOException){
                 $status = 500;

                 $response['error'] = 'Internal Server Error';
             }else if($this->isHttpException($exception)){

                 $status = $exception->getStatusCode();

                 $response['error'] = 'Resource Not Found.';
             }else{
                 $status = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 400;
             }

             return response()->json($response,$status);
         }
     }
}
