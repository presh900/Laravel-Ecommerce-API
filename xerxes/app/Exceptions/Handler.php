<?php

namespace App\Exceptions;

use App\Traits\ApiResponser;  //this
use Illuminate\Auth\Access\AuthorizationException;  //ths
use Illuminate\Auth\AuthenticationException;   //this
use Illuminate\Database\Eloquent\ModelNotFoundException;  //this
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler; //this
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponser;
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
     * Report or log an exception.
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
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if($exception instanceof ValidationException){
            return $this->convertValidationExceptionToResponse($exception,$request);
        }
        if ($exception instanceof ModelNotFoundException) {
            $modelName = strtolower(class_basename($exception->getModel()));

            return $this->errorResponse('No '. $modelName.' exists with this specific ID',404);
        }
        if($exception instanceof AuthenticationException){
            return $this->unauthenticated($request,$exception);
        }
        if($exception instanceof AuthorizationException){
            return $this->errorResponse($exception->getMessage(),403);
        }
        if($exception instanceof NotFoundHttpException){
            return $this->errorResponse('The Specified URL cannot be found',404);
        }
        if($exception instanceof MethodNotAllowedHttpException){
            return $this->errorResponse('The Specified Method for this Resource is Invalid',405);
        }
        if($exception instanceof HttpException){
            return $this->errorResponse($exception->getMessage(),$exception->getStatusCode());
        }
        if($exception instanceof QueryException){
            $errorCode = $exception->errorInfo[1];
            if($errorCode==1451){
                return $this->errorResponse('Cannot Remove this Resource Permanently,it is related to another resource',409);
            }
        }
        if(!config('app.debug')){
            return $this->errorResponse('An unexpected error has occurred. Please try again later',500);
        }
        
        return parent::render($request, $exception);
    }

    protected function convertValidationExceptionToResponse(ValidationException $e, $request)
    {
        if ($e->response) {
            return $this->errorResponse($e,422);
        }

        return $request->expectsJson()
                    ? $this->invalidJson($request, $e)
                    : $this->invalid($request, $e);
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $this->errorResponse('Unauthenticated',401);
    }
}
