<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Thuraaung\ApiHelpers\Http\Responses\ApiErrorResponse;
use Thuraaung\ApiHelpers\Http\Enums\Status;

use function strval;
use function trans;

final class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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
        $this->renderable(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return new ApiErrorResponse(
                    title: strval(trans('exceptions.titles.not_found')),
                    description: $e->getMessage(),
                    status: Status::NOT_FOUND,
                );
            }
        });

        $this->renderable(function (AuthenticationException $e) {
            return new ApiErrorResponse(
                title: strval(trans('exceptions.titles.unauthenticated')),
                description: $e->getMessage(),
                status: Status::UNAUTHORIZED,
            );
        });

        $this->renderable(function (AccessDeniedHttpException $e) {
            return new ApiErrorResponse(
                title: strval(trans('exceptions.titles.unauthorized')),
                description: $e->getMessage(),
                status: Status::FORBIDDEN,
            );
        });

        $this->renderable(function (MethodNotAllowedHttpException $e) {
            return new ApiErrorResponse(
                title: strval(trans('exceptions.titles.method_not_allowed')),
                description: $e->getMessage(),
                status: Status::METHOD_NOT_ALLOWED,
            );
        });
    }
}
