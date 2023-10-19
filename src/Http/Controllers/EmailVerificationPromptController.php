<?php

namespace FortifyPlus\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use FortifyPlus\Contracts\VerifyEmailViewResponse;
use FortifyPlus\Fortify;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \FortifyPlus\Contracts\VerifyEmailViewResponse
     */
    public function __invoke(Request $request)
    {
        return $request->user()->hasVerifiedEmail()
                    ? redirect()->intended(Fortify::redirects('email-verification'))
                    : app(VerifyEmailViewResponse::class);
    }
}
