<?php

namespace FortifyPlus;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use FortifyPlus\Contracts\EmailVerificationNotificationSentResponse as EmailVerificationNotificationSentResponseContract;
use FortifyPlus\Contracts\FailedPasswordConfirmationResponse as FailedPasswordConfirmationResponseContract;
use FortifyPlus\Contracts\FailedPasswordResetLinkRequestResponse as FailedPasswordResetLinkRequestResponseContract;
use FortifyPlus\Contracts\FailedPasswordResetResponse as FailedPasswordResetResponseContract;
use FortifyPlus\Contracts\FailedTwoFactorLoginResponse as FailedTwoFactorLoginResponseContract;
use FortifyPlus\Contracts\LockoutResponse as LockoutResponseContract;
use FortifyPlus\Contracts\LoginResponse as LoginResponseContract;
use FortifyPlus\Contracts\LogoutResponse as LogoutResponseContract;
use FortifyPlus\Contracts\PasswordConfirmedResponse as PasswordConfirmedResponseContract;
use FortifyPlus\Contracts\PasswordResetResponse as PasswordResetResponseContract;
use FortifyPlus\Contracts\PasswordUpdateResponse as PasswordUpdateResponseContract;
use FortifyPlus\Contracts\ProfileInformationUpdatedResponse as ProfileInformationUpdatedResponseContract;
use FortifyPlus\Contracts\RecoveryCodesGeneratedResponse as RecoveryCodesGeneratedResponseContract;
use FortifyPlus\Contracts\RegisterResponse as RegisterResponseContract;
use FortifyPlus\Contracts\SuccessfulPasswordResetLinkRequestResponse as SuccessfulPasswordResetLinkRequestResponseContract;
use FortifyPlus\Contracts\TwoFactorAuthenticationProvider as TwoFactorAuthenticationProviderContract;
use FortifyPlus\Contracts\TwoFactorConfirmedResponse as TwoFactorConfirmedResponseContract;
use FortifyPlus\Contracts\TwoFactorDisabledResponse as TwoFactorDisabledResponseContract;
use FortifyPlus\Contracts\TwoFactorEnabledResponse as TwoFactorEnabledResponseContract;
use FortifyPlus\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;
use FortifyPlus\Contracts\VerifyEmailResponse as VerifyEmailResponseContract;
use FortifyPlus\Contracts\VerifyEmailViewResponse;
use FortifyPlus\Http\Controllers\Admin\AdminConfirmablePasswordController;
use FortifyPlus\Http\Controllers\Admin\RegisterAdminController;
use FortifyPlus\Http\Controllers\Admin\AdminSessionAuthentication;
use FortifyPlus\Http\Controllers\Admin\AdminTwoFactorAuthenticatedSessionController;
use FortifyPlus\Http\Controllers\Admin\AdminVerifyEmailController;
use FortifyPlus\Http\Controllers\AuthenticatedSessionController;
use FortifyPlus\Http\Controllers\RegisteredUserController;
use FortifyPlus\Http\Middleware\RedirectAuthenticatedAdmin;
use FortifyPlus\Http\Responses\EmailVerificationNotificationSentResponse;
use FortifyPlus\Http\Responses\FailedPasswordConfirmationResponse;
use FortifyPlus\Http\Responses\FailedPasswordResetLinkRequestResponse;
use FortifyPlus\Http\Responses\FailedPasswordResetResponse;
use FortifyPlus\Http\Responses\FailedTwoFactorLoginResponse;
use FortifyPlus\Http\Responses\LockoutResponse;
use FortifyPlus\Http\Responses\LoginResponse;
use FortifyPlus\Http\Responses\LogoutResponse;
use FortifyPlus\Http\Responses\PasswordConfirmedResponse;
use FortifyPlus\Http\Responses\PasswordResetResponse;
use FortifyPlus\Http\Responses\PasswordUpdateResponse;
use FortifyPlus\Http\Responses\ProfileInformationUpdatedResponse;
use FortifyPlus\Http\Responses\RecoveryCodesGeneratedResponse;
use FortifyPlus\Http\Responses\RegisterResponse;
use FortifyPlus\Http\Responses\SuccessfulPasswordResetLinkRequestResponse;
use FortifyPlus\Http\Responses\TwoFactorConfirmedResponse;
use FortifyPlus\Http\Responses\TwoFactorDisabledResponse;
use FortifyPlus\Http\Responses\TwoFactorEnabledResponse;
use FortifyPlus\Http\Responses\TwoFactorLoginResponse;
use FortifyPlus\Http\Responses\VerifyEmailResponse;
use PragmaRX\Google2FA\Google2FA;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        app('router')->aliasMiddleware('fguest', RedirectAuthenticatedAdmin::class);

        $this->mergeConfigFrom(__DIR__.'/../config/fortifyplus.php', 'fortifyplus');

        $this->registerResponseBindings();

        $this->app->singleton(TwoFactorAuthenticationProviderContract::class, function ($app) {
            return new TwoFactorAuthenticationProvider(
                $app->make(Google2FA::class),
                $app->make(Repository::class)
            );
        });

        $this->app->bind(StatefulGuard::class, function () {
            return Auth::guard(config('fortifyplus.guard.user', null));
        });
    }

    /**
     * Register the response bindings.
     *
     * @return void
     */
    protected function registerResponseBindings()
    {
        $this->app->singleton(FailedPasswordConfirmationResponseContract::class, FailedPasswordConfirmationResponse::class);
        $this->app->singleton(FailedPasswordResetLinkRequestResponseContract::class, FailedPasswordResetLinkRequestResponse::class);
        $this->app->singleton(FailedPasswordResetResponseContract::class, FailedPasswordResetResponse::class);
        $this->app->singleton(FailedTwoFactorLoginResponseContract::class, FailedTwoFactorLoginResponse::class);
        $this->app->singleton(LockoutResponseContract::class, LockoutResponse::class);
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
        $this->app->singleton(LogoutResponseContract::class, LogoutResponse::class);
        $this->app->singleton(PasswordConfirmedResponseContract::class, PasswordConfirmedResponse::class);
        $this->app->singleton(PasswordResetResponseContract::class, PasswordResetResponse::class);
        $this->app->singleton(PasswordUpdateResponseContract::class, PasswordUpdateResponse::class);
        $this->app->singleton(ProfileInformationUpdatedResponseContract::class, ProfileInformationUpdatedResponse::class);
        $this->app->singleton(RecoveryCodesGeneratedResponseContract::class, RecoveryCodesGeneratedResponse::class);
        $this->app->singleton(RegisterResponseContract::class, RegisterResponse::class);
        $this->app->singleton(EmailVerificationNotificationSentResponseContract::class, EmailVerificationNotificationSentResponse::class);
        $this->app->singleton(SuccessfulPasswordResetLinkRequestResponseContract::class, SuccessfulPasswordResetLinkRequestResponse::class);
        $this->app->singleton(TwoFactorConfirmedResponseContract::class, TwoFactorConfirmedResponse::class);
        $this->app->singleton(TwoFactorDisabledResponseContract::class, TwoFactorDisabledResponse::class);
        $this->app->singleton(TwoFactorEnabledResponseContract::class, TwoFactorEnabledResponse::class);
        $this->app->singleton(TwoFactorLoginResponseContract::class, TwoFactorLoginResponse::class);
        $this->app->singleton(VerifyEmailResponseContract::class, VerifyEmailResponse::class);

        $this->app->when([RegisterAdminController::class, AdminSessionAuthentication::class, AdminConfirmablePasswordController::class, AdminTwoFactorAuthenticatedSessionController::class])
            ->needs(StatefulGuard::class)
            ->give(function () {
                return Auth::guard(config('fortifyplus.guard.admin', null));
            });

        $this->app->when(AdminSessionAuthentication::class)
            ->needs(LoginResponseContract::class)
            ->give(function () {
                return new LoginResponse('admin');
            });
        
        $this->app->when(AdminSessionAuthentication::class)
            ->needs(LogoutResponseContract::class)
            ->give(function () {
                return new LogoutResponse('admin');
            });
        
        $this->app->when(RegisterAdminController::class)
            ->needs(RegisterResponseContract::class)
            ->give(function () {
                return new RegisterResponse('admin');
            });
        
        $this->app->when(AdminVerifyEmailController::class)
            ->needs(VerifyEmailViewResponse::class)
            ->give(function () {
                return new VerifyEmailResponse('admin');
            });

        $this->app->when(AdminTwoFactorAuthenticatedSessionController::class)
            ->needs(TwoFactorLoginResponseContract::class)
            ->give(function () {
                return new TwoFactorLoginResponse('admin');
            });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->configurePublishing();
        $this->configureRoutes();
    }

    /**
     * Configure the publishable resources offered by the package.
     *
     * @return void
     */
    protected function configurePublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../stubs/fortifyplus.php' => config_path('fortifyplus.php'),
            ], 'fortify-config');

            $this->publishes([
                __DIR__.'/../stubs/CreateNewUser.php' => app_path('Actions/Fortify/CreateNewUser.php'),
                __DIR__.'/../stubs/FortifyServiceProvider.php' => app_path('Providers/FortifyServiceProvider.php'),
                __DIR__.'/../stubs/PasswordValidationRules.php' => app_path('Actions/Fortify/PasswordValidationRules.php'),
                __DIR__.'/../stubs/ResetUserPassword.php' => app_path('Actions/Fortify/ResetUserPassword.php'),
                __DIR__.'/../stubs/UpdateUserProfileInformation.php' => app_path('Actions/Fortify/UpdateUserProfileInformation.php'),
                __DIR__.'/../stubs/UpdateUserPassword.php' => app_path('Actions/Fortify/UpdateUserPassword.php'),

                __DIR__.'/../stubs/UpdateAdminProfileInformation.php' => app_path('Actions/Fortify/UpdateAdminProfileInformation.php'),
                __DIR__.'/../stubs/UpdateAdminPassword.php' => app_path('Actions/Fortify/UpdateAdminPassword.php'),
                __DIR__.'/../stubs/ResetAdminPassword.php' => app_path('Actions/Fortify/ResetAdminPassword.php'),
                __DIR__.'/../stubs/CreateNewAdmin.php' => app_path('Actions/Fortify/CreateNewAdmin.php'),

                __DIR__.'/../stubs/Admin.php' => app_path('App/Models/Admin.php'),

            ], 'fortify-support');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'fortify-migrations');
        }
    }

    /**
     * Configure the routes offered by the application.
     *
     * @return void
     */
    protected function configureRoutes()
    {
        if (Fortify::$registersRoutes) {
            Route::group([
                'namespace' => 'FortifyPlus\Http\Controllers',
                'domain' => config('fortifyplus.domain', null),
                'prefix' => config('fortifyplus.prefix'),
            ], function () {
                $this->loadRoutesFrom(__DIR__.'/../routes/routes.php');
            });
        }
    }
}
