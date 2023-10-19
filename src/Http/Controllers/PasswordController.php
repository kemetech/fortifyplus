<?php

namespace FortifyPlus\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use FortifyPlus\Contracts\PasswordUpdateResponse;
use FortifyPlus\Contracts\UpdatesUserPasswords;

class PasswordController extends Controller
{
    /**
     * The password updater class  implementation.
     *
     * @var \FortifyPlus\Contracts\UpdatesUserPasswords
     */
    protected $updater;

    /**
     * Create a new controller instance.
     *
     * @param  \FortifyPlus\Contracts\UpdatesUserPasswords  $updater
     * @return void
     */
    public function __construct(UpdatesUserPasswords $updater)
    {
        $this->updater = $updater;
    } 

    /**
     * Update the user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \FortifyPlus\Contracts\PasswordUpdateResponse
     */
    public function update(Request $request)
    {
        $this->updater->update($request->user(), $request->all());

        return app(PasswordUpdateResponse::class);
    }
}
