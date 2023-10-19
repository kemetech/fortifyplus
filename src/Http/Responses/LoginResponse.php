<?php

namespace FortifyPlus\Http\Responses;

use FortifyPlus\Contracts\LoginResponse as LoginResponseContract;
use FortifyPlus\Fortify;

class LoginResponse implements LoginResponseContract
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
                    ? response()->json(['two_factor' => false])
                    : redirect()->intended(Fortify::redirects('login', $this->type));
    }
}
