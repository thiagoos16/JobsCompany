<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use JWTAuth;

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
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        // Not found exception handler
        if ($exception instanceof NotFoundHttpException) {
            return response()->json([
                'error' => [
                    'description' => 'Invalid URI',
                    'messages' => []
                ]
            ], 404);
        }

        // Method not allowed exception handler
        if ($exception instanceof MethodNotAllowedHttpException) {
            return response()->json([
                'error' => [
                    'description' => 'Method Not Allowed',
                    'messages' => []
                ]
            ], 405);
        }

        // Method unauthorized exception handler
        if ($exception instanceof UnauthorizedHttpException) {
            // If the token is expired, then it will be refreshed and added to the headers
            //try {
                try {
                    $refreshed = JWTAuth::refresh(JWTAuth::getToken());
                    $user = JWTAuth::setToken($refreshed)->toUser();
                    header('Authorization: Bearer ' . $refreshed);
                } catch (JWTException $e) {
                    return response()->json([
                       'error' => [
                            'description' => 'Token can\'t be refreshed',
                       ]
                    ], 405);
                }
                
                return response()->json([
                    'error' => [
                        'description' => 'Token was expired',
                    ]
                 ], 405);
        }

        //TokenBlacklistedException
        //JWTException

        return parent::render($request, $exception);
    }
}
