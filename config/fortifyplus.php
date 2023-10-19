<?php

use FortifyPlus\Features;

return [ 
    'guard' => 'web',
    'middleware' => ['web'],
    'auth_middleware' => 'auth',
    'passwords' => 'users',
    'username' => 'email',
    'email' => 'email',
    'views' => true,
    'home' => '/home',
    'prefix' => '',
    'domain' => null,
    'lowercase_usernames' => false,
    'limiters' => [
        'login' => null,
    ],
    'paths' => [
        'user' => [
            'login' => null,
            'logout' => null,
            'password.request' => null,
            'password.reset' => null,
            'password.email' => null,
            'password.update' => null,
            'register' => null,
            'verification.notice' => null,
            'verification.verify' => null,
            'verification.send' => null,
            'user-profile-information.update' => null,
            'user-password.update' => null,
            'password.confirm' => null,
            'password.confirmation' => null,
            'two-factor.login' => null,
            'two-factor.enable' => null,
            'two-factor.confirm' => null,
            'two-factor.disable' => null,
            'two-factor.qr-code' => null,
            'two-factor.secret-key' => null,
            'two-factor.recovery-codes' => null,
        ],
        'admin' => [
            'login' => null,
            'logout' => null,
            'password.request' => null,
            'password.reset' => null,
            'password.email' => null,
            'password.update' => null,
            'register' => null,
            'verification.notice' => null,
            'verification.verify' => null,
            'verification.send' => null,
            'user-profile-information.update' => null,
            'user-password.update' => null,
            'password.confirm' => null,
            'password.confirmation' => null,
            'two-factor.login' => null,
            'two-factor.enable' => null,
            'two-factor.confirm' => null,
            'two-factor.disable' => null,
            'two-factor.qr-code' => null,
            'two-factor.secret-key' => null,
            'two-factor.recovery-codes' => null,
        ]
        
    ],
    'redirects' => [
        'user' => [
            'login' => null,
            'logout' => null,
            'password-confirmation' => null,
            'register' => null,
            'email-verification' => null,
            'password-reset' => null,
        ],
        'admin' => [
            'login' => null,
            'logout' => null,
            'password-confirmation' => null,
            'register' => null,
            'email-verification' => null,
            'password-reset' => null,
        ]
    ],
    'features' => [
        Features::registration(),
        Features::resetPasswords(),
        Features::emailVerification(),
        Features::updateProfileInformation(),
        Features::updatePasswords(),
        Features::twoFactorAuthentication(),
    ],
];
