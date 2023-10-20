<?php

namespace FortifyPlus;

use FortifyPlus\Contracts\Admin\CreatesNewAdmins;
use FortifyPlus\Contracts\Admin\UpdatesAdminPasswords;
use FortifyPlus\Contracts\Admin\UpdatesAdminProfileInformation;
use FortifyPlus\Contracts\ConfirmPasswordViewResponse;
use FortifyPlus\Contracts\CreatesNewUsers;
use FortifyPlus\Contracts\LoginViewResponse;
use FortifyPlus\Contracts\RegisterViewResponse;
use FortifyPlus\Contracts\RequestPasswordResetLinkViewResponse;
use FortifyPlus\Contracts\ResetPasswordViewResponse;
use FortifyPlus\Contracts\ResetsAdminPasswords;
use FortifyPlus\Contracts\TwoFactorChallengeViewResponse;
use FortifyPlus\Contracts\VerifyEmailViewResponse;
use FortifyPlus\Http\Controllers\Admin\AdminNewPasswordController;
use FortifyPlus\Http\Controllers\Admin\AdminPasswordResetLinkController;
use FortifyPlus\Http\Controllers\Admin\AdminSessionAuthentication;
use FortifyPlus\Http\Controllers\Admin\AdminTwoFactorAuthenticatedSessionController;
use FortifyPlus\Http\Controllers\Admin\RegisterAdminController;
use FortifyPlus\Http\Controllers\AuthenticatedSessionController;
use FortifyPlus\Http\Controllers\NewPasswordController;
use FortifyPlus\Http\Controllers\PasswordResetLinkController;
use FortifyPlus\Http\Controllers\RegisteredUserController;
use FortifyPlus\Http\Responses\SimpleViewResponse;

class Fortify
{
    /**
     * The callback that is responsible for building the authentication pipeline array, if applicable.
     *
     * @var callable|null
     */
    public static $authenticateThroughCallback;

    /**
     * The callback that is responsible for building the admin authentication pipeline array, if applicable.
     *
     * @var callable|null
     */
    public static $adminAuthenticateThroughCallback;

    /**
     * The callback that is responsible for validating authentication credentials, if applicable.
     *
     * @var callable|null
     */
    public static $authenticateUsingCallback;

    /**
     * The callback that is responsible for validating admin authentication credentials, if applicable.
     *
     * @var callable|null
     */
    public static $adminAuthenticateUsingCallback;

    /**
     * The callback that is responsible for confirming user passwords.
     *
     * @var callable|null
     */
    public static $confirmPasswordsUsingCallback;

    /**
     * The callback that is responsible for confirming admin passwords.
     *
     * @var callable|null
     */
    public static $adminConfirmPasswordsUsingCallback;

    /**
     * Indicates if Fortify routes will be registered.
     *
     * @var bool
     */
    public static $registersRoutes = true;

    const PASSWORD_UPDATED = 'password-updated';
    const PROFILE_INFORMATION_UPDATED = 'profile-information-updated';
    const RECOVERY_CODES_GENERATED = 'recovery-codes-generated';
    const TWO_FACTOR_AUTHENTICATION_CONFIRMED = 'two-factor-authentication-confirmed';
    const TWO_FACTOR_AUTHENTICATION_DISABLED = 'two-factor-authentication-disabled';
    const TWO_FACTOR_AUTHENTICATION_ENABLED = 'two-factor-authentication-enabled';
    const VERIFICATION_LINK_SENT = 'verification-link-sent';

    /**
     * Get the username used for authentication.
     *
     * @return string
     */
    public static function username()
    {
        return config('fortifyplus.username', 'email');
    }


    /**
     * Get the name of the user email address request variable / field.
     *
     * @return string
     */
    public static function email()
    {
        return config('fortifyplus.email.user', 'email');
    }

    /**
     * Get a completion user redirect path for a specific feature.
     *
     * @param  string  $redirect
     * @return string $type
     */
    public static function redirects(string $redirect, $type = null, $default = null)
    {
        return config('fortifyplus.redirects.'.$type.$redirect) ?? $default ?? config('fortifyplus.home.'.$type);
    }


    /**
     * Register the views for Fortify using conventional names under the given namespace.
     *
     * @param  string  $namespace
     * @return void
     */
    public static function viewNamespace(string $namespace, string $adminNamespace)
    {
        static::viewPrefix($namespace.'::', $adminNamespace.'::');
    }

    /**
     * Register the views for Fortify using conventional names under the given prefix.
     *
     * @param  string  $prefix
     * @return void
     */
    public static function viewPrefix(string $prefix, string $adminPrefix)
    {
        static::loginView($prefix.'login', $adminPrefix.'login');
        static::twoFactorChallengeView($prefix.'two-factor-challenge', $adminPrefix.'two-factor-challenge');
        static::registerView($prefix.'register', $adminPrefix.'register');
        static::requestPasswordResetLinkView($prefix.'forgot-password', $adminPrefix.'forgot-password');
        static::resetPasswordView($prefix.'reset-password', $adminPrefix.'reset-password');
        static::verifyEmailView($prefix.'verify-email');
        static::confirmPasswordView($prefix.'confirm-password');
    }

    /**
     * Specify which view should be used as the login view.
     *
     * @param  callable|string  $view
     * @return void
     */
    public static function loginView($view, $adminView)
    {
        app()->when(AdminSessionAuthentication::class)
            ->needs(LoginViewResponse::class)
            ->give(function () use ($adminView) {
                return new SimpleViewResponse($adminView);
            });
        app()->when(AuthenticatedSessionController::class)
            ->needs(LoginViewResponse::class)
            ->give(function () use ($view) {
                return new SimpleViewResponse($view);
            });
        
    }


    /**
     * Specify which view should be used as the two factor authentication challenge view.
     *
     * @param  callable|string  $view
     * @return void
     */
    public static function twoFactorChallengeView($view, $adminView)
    {
        app()->when(AdminTwoFactorAuthenticatedSessionController::class)
            ->needs(TwoFactorChallengeViewResponse::class)
            ->give(function () use ($adminView) {
                return new SimpleViewResponse($adminView);
        });
        app()->singleton(TwoFactorChallengeViewResponse::class, function () use ($view) {
            return new SimpleViewResponse($view);
        });
    }

    /**
     * Specify which view should be used as the new password view.
     *
     * @param  callable|string  $view
     * @return void
     */
    public static function resetPasswordView($view, $adminView)
    {
        app()->when(AdminNewPasswordController::class)
            ->needs(ResetPasswordViewResponse::class)
            ->give(function () use ($adminView) {
                return new SimpleViewResponse($adminView);
            });
        app()->when(NewPasswordController::class)
            ->needs(ResetPasswordViewResponse::class)
            ->give(function () use ($view) {
                return new SimpleViewResponse($view);
            });
    }

    /**
     * Specify which view should be used as the registration view.
     *
     * @param  callable|string  $view
     * @return void
     */
    public static function registerView($view, $adminView)
    {
     
        app()->when(RegisterAdminController::class)
            ->needs(RegisterViewResponse::class)
            ->give(function () use ($adminView) {
                return new SimpleViewResponse($adminView);
            });
        app()->when(RegisteredUserController::class)
            ->needs(RegisterViewResponse::class)
            ->give(function () use ($view) {
                return new SimpleViewResponse($view);
            });
    }

    /**
     * Specify which view should be used as the email verification prompt.
     *
     * @param  callable|string  $view
     * @return void
     */
    public static function verifyEmailView($view)
    {
        app()->singleton(VerifyEmailViewResponse::class, function () use ($view) {
            return new SimpleViewResponse($view);
        });
    }

    /**
     * Specify which view should be used as the password confirmation prompt.
     *
     * @param  callable|string  $view
     * @return void
     */
    public static function confirmPasswordView($view)
    {
        app()->singleton(ConfirmPasswordViewResponse::class, function () use ($view) {
            return new SimpleViewResponse($view);
        });
    }

    /**
     * Specify which view should be used as the request password reset link view.
     *
     * @param  callable|string  $view
     * @return void
     */
    public static function requestPasswordResetLinkView($view, $adminView)
    {
        app()->when(AdminPasswordResetLinkController::class)
            ->needs(RequestPasswordResetLinkViewResponse::class)
            ->give(function () use ($adminView) {
                return new SimpleViewResponse($adminView);
            });
        app()->when(PasswordResetLinkController::class)
            ->needs(RequestPasswordResetLinkViewResponse::class)
            ->give(function () use ($view) {
                return new SimpleViewResponse($view);
            });
    }

    /**
     * Register a callback that is responsible for building the authentication pipeline array.
     *
     * @param  callable  $callback
     * @return void
     */
    public static function loginThrough(callable $callback)
    {
        static::authenticateThrough($callback);
    }

    /**
     * Register a callback that is responsible for building the authentication pipeline array.
     *
     * @param  callable  $callback
     * @return void
     */
    public static function authenticateThrough(callable $callback)
    {
        static::$authenticateThroughCallback = $callback;
    }

    /**
     * Register a callback that is responsible for validating incoming authentication credentials.
     *
     * @param  callable  $callback
     * @return void
     */
    public static function authenticateUsing(callable $callback)
    {
        static::$authenticateUsingCallback = $callback;
    }

    /**
     * Register a callback that is responsible for confirming existing user passwords as valid.
     *
     * @param  callable  $callback
     * @return void
     */
    public static function confirmPasswordsUsing(callable $callback)
    {
        static::$confirmPasswordsUsingCallback = $callback;
    }

        /**
     * Register a class / callback that should be used to create new users.
     *
     * @param  string  $callback
     * @return void
     */
    public static function createUsersUsing(string $callback)
    {
        app()->singleton(CreatesNewUsers::class, $callback);
    }

       /**
     * Register a class / callback that should be used to create new users.
     *
     * @param  string  $callback
     * @return void
     */
    public static function createAdminUsing(string $callback)
    {
        app()->singleton(CreatesNewAdmins::class, $callback);
    }

    /**
     * Register a class / callback that should be used to update user profile information.
     *
     * @param  string  $callback
     * @return void
     */
    public static function updateAdminProfileInformationUsing(string $callback)
    {
        app()->singleton(UpdatesAdminProfileInformation::class, $callback);
    }

    /**
     * Register a class / callback that should be used to update user passwords.
     *
     * @param  string  $callback
     * @return void
     */
    public static function updateAdminPasswordsUsing(string $callback)
    {
        app()->singleton(UpdatesAdminPasswords::class, $callback);
    }

    /**
     * Register a class / callback that should be used to reset user passwords.
     *
     * @param  string  $callback
     * @return void
     */
    public static function resetAdminPasswordsUsing(string $callback)
    {
        app()->singleton(ResetsAdminPasswords::class, $callback);
    }


    /**
     * Register a class / callback that should be used to update user profile information.
     *
     * @param  string  $callback
     * @return void
     */
    public static function updateUserProfileInformationUsing(string $callback)
    {
        app()->singleton(UpdatesUserProfileInformation::class, $callback);
    }

    /**
     * Register a class / callback that should be used to update user passwords.
     *
     * @param  string  $callback
     * @return void
     */
    public static function updateUserPasswordsUsing(string $callback)
    {
        app()->singleton(UpdatesUserPasswords::class, $callback);
    }

    /**
     * Register a class / callback that should be used to reset user passwords.
     *
     * @param  string  $callback
     * @return void
     */
    public static function resetUserPasswordsUsing(string $callback)
    {
        app()->singleton(ResetsUserPasswords::class, $callback);
    }



    /**
     * Determine if Fortify is confirming two factor authentication configurations.
     *
     * @return bool
     */
    public static function confirmsTwoFactorAuthentication()
    {
        return Features::enabled(Features::twoFactorAuthentication()) &&
               Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');
    }

    /**
     * Configure Fortify to not register its routes.
     *
     * @return static
     */
    public static function ignoreRoutes()
    {
        static::$registersRoutes = false;

        return new static;
    }
}
