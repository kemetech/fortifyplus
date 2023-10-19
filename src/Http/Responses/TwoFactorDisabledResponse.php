<?php

namespace FortifyPlus\Http\Responses;

use Illuminate\Http\JsonResponse;
use FortifyPlus\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;
use FortifyPlus\Fortify;

class TwoFactorDisabledResponse implements TwoFactorLoginResponseContract
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
                    ? new JsonResponse('', 200)
                    : back()->with('status', Fortify::TWO_FACTOR_AUTHENTICATION_DISABLED);
    }
}
