<?php

namespace FortifyPlus\Http\Responses;

use Illuminate\Http\JsonResponse;
use FortifyPlus\Contracts\VerifyEmailResponse as VerifyEmailResponseContract;
use FortifyPlus\Fortify;

class VerifyEmailResponse implements VerifyEmailResponseContract
{
    /**
     * The name of the user type or the callable used to generate the redirect response.
     *
     * @var callable|string
     */
    protected $type;

    /**
     * Create a new response instance.
     *
     * @param  callable|string  $type
     * @return void
     */
    public function __construct($type)
    {
        $this->type = $type;
    }
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        return $request->wantsJson()
            ? new JsonResponse('', 204)
            : redirect()->intended(Fortify::redirects('email-verification', $this->type).'?verified=1');
    }
}
