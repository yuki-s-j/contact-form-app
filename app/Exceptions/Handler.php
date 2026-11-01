<?php

namespace App\Exceptions;

use Illuminate\Http\Request;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
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
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (NotFoundHttpException $e, Request $request) {
            $previous = $e->getPrevious();

            if (
                $request->is('api/v1/contacts/*')
                && $previous instanceof \Illuminate\Database\Eloquent\ModelNotFoundException
                && $previous->getModel() === \App\Models\Contact::class
            ) {
                return response()->json([
                    'error' => 'お問い合わせが見つかりませんでした。',
                ], 404);
            }
        });
    }
}
