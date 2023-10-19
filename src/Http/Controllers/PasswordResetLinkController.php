<?php

namespace FortifyPlus\Http\Controllers;

use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Password;
use FortifyPlus\Contracts\FailedPasswordResetLinkRequestResponse;
use FortifyPlus\Contracts\RequestPasswordResetLinkViewResponse;
use FortifyPlus\Contracts\SuccessfulPasswordResetLinkRequestResponse;
use FortifyPlus\Fortify;

class PasswordResetLinkController extends Controller
{
    protected $view;

    /**
     * Create a new controller instance.
     *
     * @param  \Illuminate\Contracts\Auth\StatefulGuard  $guard
     * @return void
     */
    public function __construct(RequestPasswordResetLinkViewResponse $view)
    {
        $this->view = $view;
    } 

    /**
     * Show the reset password link request view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \FortifyPlus\Contracts\RequestPasswordResetLinkViewResponse
     */
    public function create(Request $request): RequestPasswordResetLinkViewResponse
    {
        return $this->view;
    }

    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Responsable
     */
    public function store(Request $request): Responsable
    {
        $request->validate([Fortify::email() => 'required|email']);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = $this->broker()->sendResetLink(
            $request->only(Fortify::email())
        );

        return $status == Password::RESET_LINK_SENT
                    ? app(SuccessfulPasswordResetLinkRequestResponse::class, ['status' => $status])
                    : app(FailedPasswordResetLinkRequestResponse::class, ['status' => $status]);
    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    protected function broker(): PasswordBroker
    {
        return Password::broker(config('fortifyplus.passwords.user'));
    }
}
