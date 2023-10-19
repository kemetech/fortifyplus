<?php

namespace FortifyPlus\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use FortifyPlus\Contracts\ProfileInformationUpdatedResponse;
use FortifyPlus\Contracts\UpdatesUserProfileInformation;
use FortifyPlus\Fortify;

class ProfileInformationController extends Controller
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
     * The profile update response implementation.
     *
     * @var \FortifyPlus\Contracts\ProfileInformationUpdatedResponse
     */
    protected $response;

    /**
     * The profile updater class  implementation.
     *
     * @var \FortifyPlus\Contracts\UpdatesUserProfileInformation
     */
    protected $updater;


    public function __construct(UpdatesUserProfileInformation $updater, ProfileInformationUpdatedResponse $response)
    {
        $this->updater = $updater;
        $this->response = $response;
    }
    /**
     * Update the user's profile information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \FortifyPlus\Contracts\ProfileInformationUpdatedResponse
     */
    public function update(Request $request)
    {
        if (config('fortify.lowercase_usernames')) {
            $request->merge([
                Fortify::username() => Str::lower($request->{Fortify::username()}),
            ]);
        }

        $this->updater->update($request->user(), $request->all());

        return $this->response;
    }
}
