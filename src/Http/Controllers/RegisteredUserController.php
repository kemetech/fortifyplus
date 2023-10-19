<?php

namespace FortifyPlus\Http\Controllers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use FortifyPlus\Contracts\CreatesNewUsers;
use FortifyPlus\Contracts\RegisterResponse;
use FortifyPlus\Contracts\RegisterViewResponse;
use FortifyPlus\Fortify;

class RegisteredUserController extends Controller
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
     * @var \FortifyPlus\Contracts\RegisterViewResponse
     */
    protected $view;

    /**
     * The creator method implementation.
     *
     * @var \FortifyPlus\Contracts\CreatesNewUsers
     */
    protected $creator;

    /**
     * The registration response implementation.
     *
     * @var \FortifyPlus\Contracts\RegisterResponse
     */
    protected $registerResponse;


    public function __construct(StatefulGuard $guard, RegisterViewResponse $view, CreatesNewUsers $creator, RegisterResponse $register)
    {
        $this->guard = $guard;
        $this->view = $view;
        $this->creator = $creator;
        $this->registerResponse = $register;
    }

    /**
     * Show the registration view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \FortifyPlus\Contracts\RegisterViewResponse
     */
    public function create(Request $request): RegisterViewResponse
    {
        return $this->view;
    }

    /**
     * Create a new registered user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \FortifyPlus\Contracts\RegisterResponse
     */
    public function store(Request $request): RegisterResponse
    {
        if (config('fortify.lowercase_usernames')) {
            $request->merge([
                Fortify::username() => Str::lower($request->{Fortify::username()}),
            ]);
        }

        event(new Registered($user = $this->creator->create($request->all())));

        $this->guard->login($user);

        return $this->registerResponse;
    }
}
