<?php

use App\Models\User;
use PragmaRX\Google2FA\Google2FA;

test('two factor settings page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get('/settings/two-factor');

    $response->assertOk();
});

test('user can enable two factor authentication', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->post(route('two-factor.enable'));

    $response->assertSessionHasNoErrors();

    $user->refresh();

    expect($user->two_factor_secret)->not->toBeNull();
    expect($user->two_factor_recovery_codes)->not->toBeNull();
    expect($user->two_factor_confirmed_at)->toBeNull();
});

test('user can confirm two factor authentication', function () {
    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->post(route('two-factor.enable'));

    $user->refresh();

    $otpCode = app(Google2FA::class)
        ->getCurrentOtp(decrypt($user->two_factor_secret));

    $response = $this
        ->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->post(route('two-factor.confirm'), [
            'code' => $otpCode,
        ]);

    $response->assertSessionHasNoErrors()
        ->assertInertiaFlash('success_toast', 'Two-factor authentication is now active');

    $user->refresh();

    expect($user->two_factor_confirmed_at)->not->toBeNull();
    expect($user->hasEnabledTwoFactorAuthentication())->toBeTrue();
});

test('user can disable two factor authentication', function () {
    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->post(route('two-factor.enable'));

    $response = $this
        ->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->delete(route('two-factor.disable'));

    $response->assertSessionHasNoErrors();

    $user->refresh();

    expect($user->two_factor_secret)->toBeNull();
    expect($user->two_factor_recovery_codes)->toBeNull();
    expect($user->two_factor_confirmed_at)->toBeNull();
});

test('user can regenerate recovery codes', function () {
    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->post(route('two-factor.enable'));

    $user->refresh();
    $initialRecoveryCodes = $user->recoveryCodes();

    $response = $this
        ->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->post(route('two-factor.regenerate-recovery-codes'));

    $response->assertSessionHasNoErrors();

    $user->refresh();

    $this->assertNotEquals($initialRecoveryCodes, $user->recoveryCodes());
});