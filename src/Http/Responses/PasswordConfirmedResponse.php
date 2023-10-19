<?php

namespace FortifyPlus\Http\Responses;

use Illuminate\Http\JsonResponse;
use FortifyPlus\Contracts\PasswordConfirmedResponse as PasswordConfirmedResponseContract;
use FortifyPlus\Fortify;

class PasswordConfirmedResponse implements PasswordConfirmedResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        return $request->wantsJson()
                    ? new JsonResponse('', 201)
                    : redirect()->intended(Fortify::redirects('password-confirmation'));
    }
}
