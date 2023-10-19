<?php

namespace FortifyPlus\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use FortifyPlus\Actions\ConfirmTwoFactorAuthentication;
use FortifyPlus\Contracts\TwoFactorConfirmedResponse;

class ConfirmedTwoFactorAuthenticationController extends Controller
{
    /**
     * Enable two factor authentication for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \FortifyPlus\Actions\ConfirmTwoFactorAuthentication  $confirm
     * @return \FortifyPlus\Contracts\TwoFactorConfirmedResponse
     */
    public function store(Request $request, ConfirmTwoFactorAuthentication $confirm)
    {
        $confirm($request->user(), $request->input('code'));

        return app(TwoFactorConfirmedResponse::class);
    }
}
