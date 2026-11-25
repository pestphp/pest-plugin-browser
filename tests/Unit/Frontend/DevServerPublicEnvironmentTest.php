<?php

declare(strict_types=1);

use Pest\Browser\Frontend\DevServer;

afterEach(function (): void {
    putenv('VITE_FEATURE_X');
    putenv('UNRELATED_SETTING');
    putenv('NEXT_PUBLIC_API_URL');
});

it('changes the signature of the dev server when a public variable of the frontend changes', function (): void {
    $devServer = new DevServer('/tmp', 'npm run dev -- --port {port}', 'localhost', 5173, [], '/', 60, null);

    putenv('VITE_FEATURE_X=off');
    $off = $devServer->signature();

    putenv('VITE_FEATURE_X=on');
    $on = $devServer->signature();

    putenv('UNRELATED_SETTING=1');

    expect($on)->not->toBe($off)
        ->and($devServer->signature())->toBe($on);
});

it('keeps the signature when an inherited public variable is overridden by the configuration', function (): void {
    $devServer = new DevServer('/tmp', 'npm run dev', 'localhost', 5173, ['NEXT_PUBLIC_API_URL' => 'bridge'], '/', 60, null);

    putenv('NEXT_PUBLIC_API_URL=a');
    $first = $devServer->signature();

    putenv('NEXT_PUBLIC_API_URL=b');

    expect($devServer->signature())->toBe($first);
});

it('separates two configurations while a public variable holds bytes that are not valid UTF-8', function (): void {
    putenv('VITE_FEATURE_X='.chr(233));

    $first = new DevServer('/tmp', 'npm run dev', 'localhost', 5173, ['FIXTURE_BACKEND_URL' => 'http://127.0.0.1:12201'], '/', 60, null);
    $second = new DevServer('/tmp', 'npm run dev', 'localhost', 5173, ['FIXTURE_BACKEND_URL' => 'http://127.0.0.1:12202'], '/', 60, null);

    expect($first->signature())->not->toBe($second->signature());
});
