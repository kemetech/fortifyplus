<?php

namespace FortifyPlus\Http\Responses;

use Illuminate\Http\JsonResponse;
use FortifyPlus\Contracts\RecoveryCodesGeneratedResponse as RecoveryCodesGeneratedResponseContract;
use FortifyPlus\Fortify;

class RecoveryCodesGeneratedResponse implements RecoveryCodesGeneratedResponseContract
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
                    : back()->with('status', Fortify::RECOVERY_CODES_GENERATED);
    }
}
