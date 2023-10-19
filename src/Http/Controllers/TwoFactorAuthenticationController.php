<?php

namespace FortifyPlus\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use FortifyPlus\Actions\DisableTwoFactorAuthentication;
use FortifyPlus\Actions\EnableTwoFactorAuthentication;
use FortifyPlus\Contracts\TwoFactorDisabledResponse;
use FortifyPlus\Contracts\TwoFactorEnabledResponse;

class TwoFactorAuthenticationController extends Controller
{
    /**
     * Enable two factor authentication for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \FortifyPlus\Actions\EnableTwoFactorAuthentication  $enable
     * @return \FortifyPlus\Contracts\TwoFactorEnabledResponse
     */
    public function store(Request $request, EnableTwoFactorAuthentication $enable)
    {
        $enable($request->user());

        return app(TwoFactorEnabledResponse::class);
    }

    /**
     * Disable two factor authentication for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \FortifyPlus\Actions\DisableTwoFactorAuthentication  $disable
     * @return \FortifyPlus\Contracts\TwoFactorDisabledResponse
     */
    public function destroy(Request $request, DisableTwoFactorAuthentication $disable)
    {
        $disable($request->user());

        return app(TwoFactorDisabledResponse::class);
    }
}
