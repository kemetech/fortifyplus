<?php

namespace FortifyPlus\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use FortifyPlus\Contracts\EmailVerificationNotificationSentResponse;
use FortifyPlus\Fortify;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $request->wantsJson()
                        ? new JsonResponse('', 204)
                        : redirect()->intended(Fortify::redirects('email-verification'));
        }

        $request->user()->sendEmailVerificationNotification();

        return app(EmailVerificationNotificationSentResponse::class);
    }
}
