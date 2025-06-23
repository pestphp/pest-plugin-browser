<?php

declare(strict_types=1);

use Pest\Plugins\Parallel;

it('reuses the test case environment variables', function (): void {
    $page = page()->goto(route('environment-variables'));

    $testEnvironmentVariables = [
        'APP_ENV' => $appEnv = config('app.env'),
        'APP_DEBUG' => $appDebug = config('app.debug'),
        'TEST_TOKEN' => $testToken = (Parallel::isWorker() ? $_SERVER['TEST_TOKEN'] : false),
    ];

    $pageEnvironmentVariables = json_decode((string) $page->textContent(), true);

    expect($pageEnvironmentVariables)->toBe($testEnvironmentVariables)
        ->and($appEnv)->toBe('testing')
        ->and($appDebug)->toBe(true);

    if (Parallel::isWorker()) {
        expect($testToken)->toBeString();
    } else {
        expect($testToken)->toBeFalse();
    }
});
