<?php

namespace FortifyPlus\Http\Controllers\Admin;

use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Support\Facades\Password;
use FortifyPlus\Http\Controllers\PasswordResetLinkController;

class AdminPasswordResetLinkController extends PasswordResetLinkController
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
