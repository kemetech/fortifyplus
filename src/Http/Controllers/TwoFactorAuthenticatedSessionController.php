<?php

namespace FortifyPlus\Http\Controllers;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Routing\Controller;
use FortifyPlus\Contracts\FailedTwoFactorLoginResponse;
use FortifyPlus\Contracts\TwoFactorChallengeViewResponse;
use FortifyPlus\Contracts\TwoFactorLoginResponse;
use FortifyPlus\Events\RecoveryCodeReplaced;
use FortifyPlus\Http\Requests\TwoFactorLoginRequest;

class TwoFactorAuthenticatedSessionController extends Controller
{
    /**
     * The guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected $guard;

    /**
     * Create a new controller instance.
     *
     * @param  \Illuminate\Contracts\Auth\StatefulGuard  $guard
     * @return void
     */
    public function __construct(StatefulGuard $guard)
    {
        $this->guard = $guard;
    }

    /**
     * Show the two factor authentication challenge view.
     *
     * @param  \FortifyPlus\Http\Requests\TwoFactorLoginRequest  $request
     * @return \FortifyPlus\Contracts\TwoFactorChallengeViewResponse
     */
    public function create(TwoFactorLoginRequest $request): TwoFactorChallengeViewResponse
    {
        if (! $request->hasChallengedUser()) {
            throw new HttpResponseException(redirect()->route('login'));
        }

        return app(TwoFactorChallengeViewResponse::class);
    }

    /**
     * Attempt to authenticate a new session using the two factor authentication code.
     *
     * @param  \FortifyPlus\Http\Requests\TwoFactorLoginRequest  $request
     * @return mixed
     */
    public function store(TwoFactorLoginRequest $request)
    {
        $user = $request->challengedUser();

        if ($code = $request->validRecoveryCode()) {
            $user->replaceRecoveryCode($code);

            event(new RecoveryCodeReplaced($user, $code));
        } elseif (! $request->hasValidCode()) {
            return app(FailedTwoFactorLoginResponse::class)->toResponse($request);
        }

        $this->guard->login($user, $request->remember());

        $request->session()->regenerate();

        return app(TwoFactorLoginResponse::class);
    }
}
