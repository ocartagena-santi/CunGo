<?php

use App\Enums\UserRole;
use App\Models\User;

test('users default to the user role', function () {
    $user = User::factory()->create();

    expect($user->role)->toBe(UserRole::User)
        ->and($user->isAdmin())->toBeFalse()
        ->and($user->isOperator())->toBeFalse();
});

test('the admin factory state creates an administrator', function () {
    $user = User::factory()->admin()->create();

    expect($user->role)->toBe(UserRole::Admin)
        ->and($user->isAdmin())->toBeTrue()
        ->and($user->isOperator())->toBeFalse();
});

test('the operator factory state creates an operator', function () {
    $user = User::factory()->operator()->create();

    expect($user->role)->toBe(UserRole::Operator)
        ->and($user->isOperator())->toBeTrue()
        ->and($user->isAdmin())->toBeFalse();
});
