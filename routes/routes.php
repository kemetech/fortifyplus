<?php

use Illuminate\Support\Facades\Route;
use FortifyPlus\Features;
use FortifyPlus\Http\Controllers\Admin\AdminConfirmablePasswordController;
use FortifyPlus\Http\Controllers\Admin\AdminNewPasswordController;
use FortifyPlus\Http\Controllers\Admin\AdminPasswordController;
use FortifyPlus\Http\Controllers\Admin\AdminPasswordResetLinkController;
use FortifyPlus\Http\Controllers\Admin\AdminProfileInformationController;
use FortifyPlus\Http\Controllers\Admin\RegisterAdminController;
use FortifyPlus\Http\Controllers\Admin\AdminSessionAuthentication;
use FortifyPlus\Http\Controllers\Admin\AdminTwoFactorAuthenticatedSessionController;
use FortifyPlus\Http\Controllers\Admin\AdminVerifyEmailController;
use FortifyPlus\Http\Controllers\AuthenticatedSessionController;
use FortifyPlus\Http\Controllers\ConfirmablePasswordController;
use FortifyPlus\Http\Controllers\ConfirmedPasswordStatusController;
use FortifyPlus\Http\Controllers\ConfirmedTwoFactorAuthenticationController;
use FortifyPlus\Http\Controllers\EmailVerificationNotificationController;
use FortifyPlus\Http\Controllers\EmailVerificationPromptController;
use FortifyPlus\Http\Controllers\NewPasswordController;
use FortifyPlus\Http\Controllers\PasswordController;
use FortifyPlus\Http\Controllers\PasswordResetLinkController;
use FortifyPlus\Http\Controllers\ProfileInformationController;
use FortifyPlus\Http\Controllers\RecoveryCodeController;
use FortifyPlus\Http\Controllers\RegisteredUserController;
use FortifyPlus\Http\Controllers\TwoFactorAuthenticatedSessionController;
use FortifyPlus\Http\Controllers\TwoFactorAuthenticationController;
use FortifyPlus\Http\Controllers\TwoFactorQrCodeController;
use FortifyPlus\Http\Controllers\TwoFactorSecretKeyController;
use FortifyPlus\Http\Controllers\VerifyEmailController;
use FortifyPlus\RoutePath;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

Route::group(['middleware' => config('fortifyplus.middleware', ['web'])], function () {
    $enableViews = config('fortifyplus.views', true);

    // Authentication... 
    if ($enableViews) {
        Route::get(RoutePath::for('login', '/login'), [AuthenticatedSessionController::class, 'create'])
            ->middleware(['fguest:'.config('fortifyplus.guard.user')])
            ->name('login');

        Route::get(RoutePath::forAdmin('login', '/admin/login'), [AdminSessionAuthentication::class, 'create'])
            ->middleware(['fguest:'.config('fortifyplus.guard.admin')])
            ->name('admin.login');
    }

    $limiter = config('fortifyplus.limiters.login');

    $twoFactorLimiter = config('fortifyplus.limiters.two-factor');

    $verificationLimiter = config('fortifyplus.limiters.verification', '6,1');


    Route::post(RoutePath::for('login', '/login'), [AuthenticatedSessionController::class, 'store'])
        ->middleware(array_filter([
            'fguest:'.config('fortifyplus.guard.user'),
            $limiter ? 'throttle:'.$limiter : null,
        ]));
    Route::post(RoutePath::forAdmin('login', '/admin/login'), [AdminSessionAuthentication::class, 'store'])
    ->middleware(array_filter([
        'fguest:'.config('fortifyplus.guard.admin'),
        $limiter ? 'throttle:'.$limiter : null,
    ]));

    Route::post(RoutePath::for('logout', '/logout'), [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout');
    Route::post(RoutePath::forAdmin('logout', '/admin/logout'), [AdminSessionAuthentication::class, 'destroy'])
        ->name('admin.logout');

    // Password Reset...
    if (Features::enabled(Features::resetPasswords())) {
        if ($enableViews) {
            Route::get(RoutePath::for('password.request', '/forgot-password'), [PasswordResetLinkController::class, 'create'])
                ->middleware(['fguest:'.config('fortifyplus.guard.user')])
                ->name('password.request');
            Route::get(RoutePath::for('password.reset', '/reset-password/{token}'), [NewPasswordController::class, 'create'])
                ->middleware(['fguest:'.config('fortifyplus.guard.user')])
                ->name('password.reset');
            Route::get(RoutePath::forAdmin('password.request', '/admin/forgot-password'), [AdminPasswordResetLinkController::class, 'create'])
                ->middleware(['fguest:'.config('fortifyplus.guard.admin')])
                ->name('admin.password.request');
            Route::get(RoutePath::forAdmin('password.reset', '/admin/reset-password/{token}'), [AdminNewPasswordController::class, 'create'])
                ->middleware(['fguest:'.config('fortifyplus.guard.admin')])
                ->name('admin.password.reset');
        }

        Route::post(RoutePath::for('password.email', '/forgot-password'), [PasswordResetLinkController::class, 'store'])
        ->middleware(['fguest:'.config('fortify.guard')])
        ->name('password.email');

        Route::post(RoutePath::forAdmin('password.email', '/admin/forgot-password'), [AdminPasswordResetLinkController::class, 'store'])
        ->middleware(['fguest:'.config('fortify.guard')])
        ->name('admin.password.email');

        

        Route::post(RoutePath::for('password.update', '/reset-password'), [NewPasswordController::class, 'store'])
            ->middleware(['fguest:'.config('fortify.guard')])
            ->name('password.update');
    }

    // Registration...
    if (Features::enabled(Features::registration())) {
        if ($enableViews) {
            Route::get(RoutePath::for('register', '/register'), [RegisteredUserController::class, 'create'])
                ->middleware(['fguest:'.config('fortifyplus.guard.user')])
                ->name('register');
            Route::get(RoutePath::forAdmin('register', '/admin/register'), [RegisterAdminController::class, 'create'])
                ->middleware(['fguest:'.config('fortifyplus.guard.admin')])
                ->name('admin.register');
        }

        Route::post(RoutePath::for('register', '/register'), [RegisteredUserController::class, 'store'])
            ->middleware(['fguest:'.config('fortifyplus.guard.user')]);
        Route::post(RoutePath::forAdmin('register', '/admin/register'), [RegisterAdminController::class, 'store'])
            ->middleware(['fguest:'.config('fortifyplus.guard.admin')]);

    }

    // Email Verification...
    if (Features::enabled(Features::emailVerification())) {
        if ($enableViews) {
            Route::get(RoutePath::for('verification.notice', '/email/verify'), [EmailVerificationPromptController::class, '__invoke'])
                ->middleware([config('fortify.auth_middleware.user', 'auth').':'.config('fortifyplus.guard.user')])
                ->name('verification.notice');

            Route::get(RoutePath::forAdmin('verification.notice', '/email/verify'), [EmailVerificationPromptController::class, '__invoke'])
                ->middleware([config('fortify.auth_middleware.admin', 'fauth').':'.config('fortifyplus.guard.admin')])
                ->name('admin.verification.notice');
        }

        Route::get(RoutePath::for('verification.verify', '/email/verify/{id}/{hash}'), [VerifyEmailController::class, '__invoke'])
            ->middleware([config('fortify.auth_middleware.user', 'auth').':'.config('fortify.guard.user'), 'signed', 'throttle:'.$verificationLimiter])
            ->name('verification.verify');

        Route::post(RoutePath::for('verification.send', '/email/verification-notification'), [EmailVerificationNotificationController::class, 'store'])
            ->middleware([config('fortify.auth_middleware.user', 'auth').':'.config('fortify.guard.user'), 'throttle:'.$verificationLimiter])
            ->name('verification.send');

        Route::get(RoutePath::forAdmin('verification.verify', '/admin/email/verify/{id}/{hash}'), [AdminVerifyEmailController::class, '__invoke'])
            ->middleware([config('fortifyplus.auth_middleware.admin', 'fauth').':'.config('fortify.guard.admin'), 'signed', 'throttle:'.$verificationLimiter])
            ->name('admin.verification.verify');

        Route::post(RoutePath::forAdmin('verification.send', '/admin/email/verification-notification'), [EmailVerificationNotificationController::class, 'store'])
            ->middleware([config('fortifyplus.auth_middleware.admin' , 'fauth').':'.config('fortifyplus.guard.admin'), 'throttle:'.$verificationLimiter])
            ->name('admin.verification.send');
    }

    // Profile Information...
    if (Features::enabled(Features::updateProfileInformation())) {
        Route::put(RoutePath::for('user-profile-information.update', '/user/profile-information'), [ProfileInformationController::class, 'update'])
            ->middleware([config('fortify.auth_middleware.user', 'auth').':'.config('fortifyplus.guard.user')])
            ->name('user-profile-information.update');

        Route::put(RoutePath::forAdmin('user-profile-information.update', '/admin/profile-information'), [AdminProfileInformationController::class, 'update'])
            ->middleware([config('fortify.auth_middleware.admin', 'fauth').':'.config('fortifyplus.guard.admin')])
            ->name('admin-profile-information.update');
    }
    

    // Passwords...
    if (Features::enabled(Features::updatePasswords())) {
        Route::put(RoutePath::for('user-password.update', '/user/password'), [PasswordController::class, 'update'])
            ->middleware([config('fortify.auth_middleware.user', 'auth').':'.config('fortifyplus.guard.user')])
            ->name('user-password.update');

        Route::put(RoutePath::forAdmin('user-password.update', '/admin/password'), [AdminPasswordController::class, 'update'])
            ->middleware([config('fortify.auth_middleware.admin', 'fauth').':'.config('fortifyplus.guard.admin')])
            ->name('admin-password.update');
    }

    // Password Confirmation...
    if ($enableViews) {
        Route::get(RoutePath::for('password.confirm', '/user/confirm-password'), [ConfirmablePasswordController::class, 'show'])
            ->middleware([config('fortify.auth_middleware.user', 'auth').':'.config('fortifyplus.guard.user')]);

        Route::get(RoutePath::forAdmin('password.confirm', '/admin/confirm-password'), [AdminConfirmablePasswordController::class, 'show'])
            ->middleware([config('fortify.auth_middleware.admin', 'fauth').':'.config('fortifyplus.guard.admin')]);
    }

    Route::get(RoutePath::for('password.confirmation', '/user/confirmed-password-status'), [ConfirmedPasswordStatusController::class, 'show'])
        ->middleware([config('fortify.auth_middleware.user', 'auth').':'.config('fortifyplus.guard.user')])
        ->name('password.confirmation');

    Route::post(RoutePath::for('password.confirm', '/user/confirm-password'), [ConfirmablePasswordController::class, 'store'])
        ->middleware([config('fortify.auth_middleware.user', 'auth').':'.config('fortifyplus.guard.user')])
        ->name('password.confirm');


    Route::get(RoutePath::forAdmin('password.confirmation', '/admin/confirmed-password-status'), [ConfirmedPasswordStatusController::class, 'show'])
        ->middleware([config('fortify.auth_middleware.admin', 'fauth').':'.config('fortifyplus.guard.admin')])
        ->name('admin.password.confirmation');

    Route::post(RoutePath::forAdmin('password.confirm', '/admin/confirm-password'), [AdminConfirmablePasswordController::class, 'store'])
        ->middleware([config('fortify.auth_middleware.admin', 'fauth').':'.config('fortifyplus.guard.admin')])
        ->name('admin.password.confirm');

    // Two Factor Authentication...
    if (Features::enabled(Features::twoFactorAuthentication())) {
        if ($enableViews) {
            Route::get(RoutePath::for('two-factor.login', '/two-factor-challenge'), [TwoFactorAuthenticatedSessionController::class, 'create'])
                ->middleware(['fguest:'.config('fortifyplus.guard.user')])
                ->name('two-factor.login');

            Route::get(RoutePath::forAdmin('two-factor.login', '/admin/two-factor-challenge'), [AdminTwoFactorAuthenticatedSessionController::class, 'create'])
                ->middleware(['fguest:'.config('fortifyplus.guard.admin')])
                ->name('admin.two-factor.login');
        }

        Route::post(RoutePath::for('two-factor.login', '/two-factor-challenge'), [TwoFactorAuthenticatedSessionController::class, 'store'])
            ->middleware(array_filter([
                'fguest:'.config('fortifyplus.guard.user'),
                $twoFactorLimiter ? 'throttle:'.$twoFactorLimiter : null,
            ]));
        Route::post(RoutePath::forAdmin('two-factor.login', '/admin/two-factor-challenge'), [AdminTwoFactorAuthenticatedSessionController::class, 'store'])
            ->middleware(array_filter([
                'fguest:'.config('fortifyplus.guard.admin'),
                $twoFactorLimiter ? 'throttle:'.$twoFactorLimiter : null,
            ]));

        $twoFactorMiddleware = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')
            ? [config('fortify.auth_middleware.user', 'auth').':'.config('fortifyplus.guard.user'), 'password.confirm']
            : [config('fortify.auth_middleware.user', 'auth').':'.config('fortifyplus.guard.user')];

        $adminTwoFactorMiddleware = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')
            ? [config('fortify.auth_middleware.admin', 'fauth').':'.config('fortifyplus.guard.admin'), 'password.confirm']
            : [config('fortify.auth_middleware.admin', 'fauth').':'.config('fortifyplus.guard.admin')];

        Route::post(RoutePath::for('two-factor.enable', '/user/two-factor-authentication'), [TwoFactorAuthenticationController::class, 'store'])
            ->middleware($twoFactorMiddleware)
            ->name('two-factor.enable');

        Route::post(RoutePath::for('two-factor.confirm', '/user/confirmed-two-factor-authentication'), [ConfirmedTwoFactorAuthenticationController::class, 'store'])
            ->middleware($twoFactorMiddleware)
            ->name('two-factor.confirm');

        Route::delete(RoutePath::for('two-factor.disable', '/user/two-factor-authentication'), [TwoFactorAuthenticationController::class, 'destroy'])
            ->middleware($twoFactorMiddleware)
            ->name('two-factor.disable');

        Route::get(RoutePath::for('two-factor.qr-code', '/user/two-factor-qr-code'), [TwoFactorQrCodeController::class, 'show'])
            ->middleware($twoFactorMiddleware)
            ->name('two-factor.qr-code');

        Route::get(RoutePath::for('two-factor.secret-key', '/user/two-factor-secret-key'), [TwoFactorSecretKeyController::class, 'show'])
            ->middleware($twoFactorMiddleware)
            ->name('two-factor.secret-key');

        Route::get(RoutePath::for('two-factor.recovery-codes', '/user/two-factor-recovery-codes'), [RecoveryCodeController::class, 'index'])
            ->middleware($twoFactorMiddleware)
            ->name('two-factor.recovery-codes');

        Route::post(RoutePath::for('two-factor.recovery-codes', '/user/two-factor-recovery-codes'), [RecoveryCodeController::class, 'store'])
            ->middleware($twoFactorMiddleware);

        Route::post(RoutePath::forAdmin('two-factor.enable', '/admin/two-factor-authentication'), [TwoFactorAuthenticationController::class, 'store'])
            ->middleware($adminTwoFactorMiddleware)
            ->name('admin.two-factor.enable');

        Route::post(RoutePath::forAdmin('two-factor.confirm', '/admin/confirmed-two-factor-authentication'), [ConfirmedTwoFactorAuthenticationController::class, 'store'])
            ->middleware($adminTwoFactorMiddleware)
            ->name('admin.two-factor.confirm');

        Route::delete(RoutePath::forAdmin('two-factor.disable', '/admin/two-factor-authentication'), [TwoFactorAuthenticationController::class, 'destroy'])
            ->middleware($adminTwoFactorMiddleware)
            ->name('admin.two-factor.disable');

        Route::get(RoutePath::forAdmin('two-factor.qr-code', '/admin/two-factor-qr-code'), [TwoFactorQrCodeController::class, 'show'])
            ->middleware($adminTwoFactorMiddleware)
            ->name('admin.two-factor.qr-code');

        Route::get(RoutePath::forAdmin('two-factor.secret-key', '/admin/two-factor-secret-key'), [TwoFactorSecretKeyController::class, 'show'])
            ->middleware($adminTwoFactorMiddleware)
            ->name('admin.two-factor.secret-key');

        Route::get(RoutePath::forAdmin('two-factor.recovery-codes', '/admin/two-factor-recovery-codes'), [RecoveryCodeController::class, 'index'])
            ->middleware($adminTwoFactorMiddleware)
            ->name('admin.two-factor.recovery-codes');

        Route::post(RoutePath::forAdmin('two-factor.recovery-codes', '/admin/two-factor-recovery-codes'), [RecoveryCodeController::class, 'store'])
            ->middleware($adminTwoFactorMiddleware);

            
    }
});
