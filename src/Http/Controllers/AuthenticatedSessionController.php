<?php

namespace FortifyPlus\Http\Controllers;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Pipeline;
use FortifyPlus\Actions\AttemptToAuthenticate;
use FortifyPlus\Actions\CanonicalizeUsername;
use FortifyPlus\Actions\EnsureLoginIsNotThrottled;
use FortifyPlus\Actions\PrepareAuthenticatedSession;
use FortifyPlus\Actions\RedirectIfTwoFactorAuthenticatable;
use FortifyPlus\Contracts\LoginResponse;
use FortifyPlus\Contracts\LoginViewResponse;
use FortifyPlus\Contracts\LogoutResponse;
use FortifyPlus\Features;
use FortifyPlus\Fortify;
use FortifyPlus\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * The guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\StatefulGuard
     */

    protected $guard;

    /**
     * The guard implementation.
     *
     * @var \FortifyPlus\Contracts\LoginViewResponse
     */
    protected $view;

    /**
     * The guard implementation.
     *
     * @var \FortifyPlus\Contracts\LoginResponse
     */
    protected $loginResponse;

    /**
     * The guard implementation.
     *
     * @var \FortifyPlus\Contracts\LogoutResponse
     */
    protected $logoutResponse;

    /**
     * Create a new controller instance.
     *
     * @param  \Illuminate\Contracts\Auth\StatefulGuard  $guard
     * @return void
     */
    public function __construct(StatefulGuard $guard, LoginViewResponse $view, LoginResponse $login, LogoutResponse $logout)
    {
        $this->guard = $guard;
        $this->view = $view;
        $this->loginResponse = $login;
        $this->logoutResponse = $logout;
    }

    /**
     * Show the login view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \FortifyPlus\Contracts\LoginViewResponse
     */
    public function create(Request $request): LoginViewResponse
    {
        return $this->view;
    }

    /**
     * Attempt to authenticate a new session.
     *
     * @param  \FortifyPlus\Http\Requests\LoginRequest  $request
     * @return mixed
     */
    public function store(LoginRequest $request)
    {
        return $this->loginPipeline($request)->then(function ($request) {
            return $this->loginResponse;
        });
    }

    /**
     * Get the authentication pipeline instance.
     *
     * @param  \FortifyPlus\Http\Requests\LoginRequest  $request
     * @return \Illuminate\Pipeline\Pipeline
     */
    protected function loginPipeline(LoginRequest $request)
    {
        $this->customLoginPipeline($request);

        return (new Pipeline(app()))->send($request)->through(array_filter([
            config('fortify.limiters.login') ? null : EnsureLoginIsNotThrottled::class,
            config('fortify.lowercase_usernames') ? CanonicalizeUsername::class : null,
            Features::enabled(Features::twoFactorAuthentication()) ? app()->make(RedirectIfTwoFactorAuthenticatable::class, ['guard' => $this->guard])  : null,
            app()->make(AttemptToAuthenticate::class, ['guard' => $this->guard]),
            PrepareAuthenticatedSession::class,
        ]));
    }

    /**
     * Get the custom authentication pipeline instance.
     *
     * @param  \FortifyPlus\Http\Requests\LoginRequest  $request
     * @return \Illuminate\Pipeline\Pipeline
     */
    protected function customLoginPipeline(LoginRequest $request)
    {
        if (Fortify::$authenticateThroughCallback) {
            return (new Pipeline(app()))->send($request)->through(array_filter(
                call_user_func(Fortify::$authenticateThroughCallback, $request)
            ));
        }

        if (is_array(config('fortify.pipelines.user.login'))) {
            return (new Pipeline(app()))->send($request)->through(array_filter(
                config('fortify.pipelines.user.login')
            ));
        }

    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \FortifyPlus\Contracts\LogoutResponse
     */
    public function destroy(Request $request): LogoutResponse
    {
        $this->guard->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return $this->logoutResponse;
    }
}

