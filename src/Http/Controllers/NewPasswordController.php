<?php

namespace FortifyPlus\Http\Controllers;

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

class NewPasswordController extends Controller
{
    /**
     * The guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected $guard;

    /**
     * The view implementation.
     *
     * @var \FortifyPlus\Contracts\ResetPasswordViewResponse
     */
    protected $view;

    /**
     * The view implementation.
     *
     * @var \FortifyPlus\Contracts\ResetsUserPasswords
     */
    protected $updater;

    /**
     * Create a new controller instance.
     *
     * @param  \Illuminate\Contracts\Auth\StatefulGuard  $guard
     * @return void
     */
    public function __construct(StatefulGuard $guard, ResetPasswordViewResponse $view, ResetsUserPasswords $updater)
    {
        $this->guard = $guard;
        $this->view = $view;
        $this->updater = $updater;
    }

    /**
     * Show the new password view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \FortifyPlus\Contracts\ResetPasswordViewResponse
     */
    public function create(Request $request): ResetPasswordViewResponse
    {
        return $this->view;
    }

    /**
     * Reset the user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Responsable
     */
    public function store(Request $request): Responsable
    {
        $request->validate([
            'token' => 'required',
            Fortify::email() => 'required|email', 
            'password' => 'required',
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = $this->broker()->reset(
            $request->only(Fortify::email(), 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                app($this->updater)->reset($user, $request->all());

                app(CompletePasswordReset::class)($this->guard, $user);
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $status == Password::PASSWORD_RESET
                    ? app(PasswordResetResponse::class, ['status' => $status])
                    : app(FailedPasswordResetResponse::class, ['status' => $status]);
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
