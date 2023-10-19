<?php

namespace FortifyPlus\Http\Controllers\Admin;

use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Password;
use FortifyPlus\Actions\CompletePasswordReset;
use FortifyPlus\Contracts\FailedPasswordResetResponse;
use FortifyPlus\Contracts\PasswordResetResponse;
use FortifyPlus\Contracts\ResetPasswordViewResponse;
use FortifyPlus\Contracts\ResetsUserPasswords;
use FortifyPlus\Fortify;
use FortifyPlus\Http\Controllers\NewPasswordController;

class AdminNewPasswordController extends NewPasswordController
{
    
    /**
     * Get the broker to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    protected function broker(): PasswordBroker
    {
        return Password::broker(config('fortifyplus.passwords.admin'));
    }
}
