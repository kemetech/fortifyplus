<?php

namespace FortifyPlus\Http\Controllers\Admin;

use Illuminate\Routing\Pipeline;
use FortifyPlus\Fortify;
use FortifyPlus\Http\Controllers\AuthenticatedSessionController;
use FortifyPlus\Http\Requests\LoginRequest;

class AdminSessionAuthentication extends AuthenticatedSessionController
{    

    /**
     * Get the custom authentication pipeline instance.
     *
     * @param  \FortifyPlus\Http\Requests\LoginRequest  $request
     * @return \Illuminate\Pipeline\Pipeline
     */
    protected function customLoginPipeline(LoginRequest $request)
    {
        if (Fortify::$adminAuthenticateThroughCallback) {
            return (new Pipeline(app()))->send($request)->through(array_filter(
                call_user_func(Fortify::$adminAuthenticateThroughCallback, $request)
            ));
        }

        if (is_array(config('fortifyplus.pipelines.admin.login'))) {
            return (new Pipeline(app()))->send($request)->through(array_filter(
                config('fortifyplus.pipelines.admin.login')
            ));
        }

    }

   
}
