<?php

use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia;
use Symfony\Component\HttpFoundation\Response;

test('inertia mutation requests receive configured error payload for known statuses', function () {
    $response = $this->withHeaders([
        'X-Inertia' => 'true',
    ])->post('/missing-endpoint');

    $response->assertNotFound()
        ->assertJsonPath('status', 404)
        ->assertJsonPath('errorSummary', (Response::$statusTexts[404] ?? 'Error') . ' - 404')
        ->assertJsonPath('errorDetail', config('errors.statuses.404.detail'));
});

test('inertia mutation requests use fallback metadata for unknown statuses', function () {
    Route::post('/_tests/error-418', fn () => abort(418));

    $response = $this->withHeaders([
        'X-Inertia' => 'true',
    ])->post('/_tests/error-418');

    $response->assertStatus(418)
        ->assertJsonPath('status', 418)
        ->assertJsonPath('errorSummary', (Response::$statusTexts[418] ?? 'Error') . ' - 418')
        ->assertJsonPath('errorDetail', config('errors.defaults.4xx.detail'));
});

test('session expired errors redirect back with configured inertia flash message', function () {
    Route::post('/_tests/error-419', fn () => abort(419));

    $response = $this->from(route('welcome', absolute: false))->post('/_tests/error-419');

    $response->assertRedirect(route('welcome', absolute: false))
        ->assertInertiaFlash('warn_message', config('errors.statuses.419.detail'));
});

test('error page receives resolved error metadata for get requests', function () {
    $response = $this->get('/missing-page');

    $response->assertNotFound()
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Error', false)
                ->where('title', Response::$statusTexts[404] ?? 'Error')
                ->where('detail', config('errors.statuses.404.detail'))
                ->where('status', 404)
        );
});
