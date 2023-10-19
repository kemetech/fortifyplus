<?php

namespace FortifyPlus\Http\Responses;

use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use FortifyPlus\Contracts\LockoutResponse as LockoutResponseContract;
use FortifyPlus\Fortify;
use FortifyPlus\LoginRateLimiter;

class LockoutResponse implements LockoutResponseContract
{
    /**
     * The login rate limiter instance.
     *
     * @var \FortifyPlus\LoginRateLimiter
     */
    protected $limiter;

    /**
     * Create a new response instance.
     *
     * @param  \FortifyPlus\LoginRateLimiter  $limiter
     * @return void
     */
    public function __construct(LoginRateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        return with($this->limiter->availableIn($request), function ($seconds) {
            throw ValidationException::withMessages([
                Fortify::username() => [
                    trans('auth.throttle', [
                        'seconds' => $seconds,
                        'minutes' => ceil($seconds / 60),
                    ]),
                ],
            ])->status(Response::HTTP_TOO_MANY_REQUESTS);
        });
    }
}
