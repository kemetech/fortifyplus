<?php

namespace FortifyPlus\Http\Controllers;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use FortifyPlus\Actions\ConfirmPassword;
use FortifyPlus\Contracts\ConfirmPasswordViewResponse;
use FortifyPlus\Contracts\FailedPasswordConfirmationResponse;
use FortifyPlus\Contracts\PasswordConfirmedResponse;

class ConfirmablePasswordController extends Controller
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
     * @var \FortifyPlus\Contracts\ConfirmPasswordViewResponse
     */
    protected $view;

    /**
     * Create a new controller instance.
     *
     * @param  \Illuminate\Contracts\Auth\StatefulGuard  $guard
     * @param  \FortifyPlus\Contracts\ConfirmPasswordViewResponse  $view
     * @return void
     */
    public function __construct(StatefulGuard $guard, ConfirmPasswordViewResponse $view)
    {
        $this->guard = $guard;
        $this->view = $view;
    }

    /**
     * Show the confirm password view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \FortifyPlus\Contracts\ConfirmPasswordViewResponse
     */
    public function show(Request $request)
    {
        return $this->view;
    }

    /**
     * Confirm the user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Responsable
     */
    public function store(Request $request)
    {
        $confirmed = app(ConfirmPassword::class)(
            $this->guard, $request->user(), $request->input('password')
        );

        if ($confirmed) {
            $request->session()->put('auth.password_confirmed_at', time());
        }

        return $confirmed
                    ? app(PasswordConfirmedResponse::class)
                    : app(FailedPasswordConfirmationResponse::class);
    }
}
