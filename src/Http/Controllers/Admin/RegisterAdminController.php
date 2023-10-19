<?php

namespace FortifyPlus\Http\Controllers\Admin;

use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use FortifyPlus\Contracts\CreatesNewUsers;
use FortifyPlus\Contracts\RegisterResponse;
use FortifyPlus\Contracts\RegisterViewResponse;
use FortifyPlus\Fortify;
use FortifyPlus\Http\Controllers\RegisteredUserController;

class RegisterAdminController extends RegisteredUserController
{
    //    
}
