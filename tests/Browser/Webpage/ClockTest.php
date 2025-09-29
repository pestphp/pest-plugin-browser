<?php

declare(strict_types=1);

use DateTimeImmutable;
use Illuminate\Support\Facades\Route;

it('can install clock and advance time with runFor', function (): void {
    Route::get('/', fn (): string => '
        <div id="timestamp"></div>
        <script>
            document.getElementById("timestamp").textContent = Date.now();
        </script>
    ');

    $page = visit('/');

    // Install clock with a specific time
    $page->clock()->install(['time' => new DateTimeImmutable('2021-01-01 00:00:00')]);

    // Reload to see the fake time
    $page->reload();

    $initialTime = (int) $page->evaluate('() => Date.now()');

    // Advance time by 5 seconds (5000ms)
    $page->clock()->runFor(5000);

    $newTime = (int) $page->evaluate('() => Date.now()');

    expect($newTime - $initialTime)->toBe(5000);
});

it('can set fixed time', function (): void {
    Route::get('/', fn (): string => '<div></div>');

    $page = visit('/');

    $fixedTime = new DateTimeImmutable('2021-01-01 00:00:00');

    $page->clock()->setFixedTime($fixedTime);

    $currentTime = (int) $page->evaluate('() => Date.now()');

    expect($currentTime)->toBe($fixedTime->getTimestamp() * 1000);
});

it('can pause and resume timers', function (): void {
    Route::get('/', fn (): string => '
        <div id="counter">0</div>
        <script>
            let counter = 0;
            setInterval(() => {
                counter++;
                document.getElementById("counter").textContent = counter;
            }, 1000);
        </script>
    ');

    $page = visit('/');

    $page->clock()->install();
    $page->reload();

    // Pause time at current moment
    $currentTime = (int) $page->evaluate('() => Date.now()');
    $page->clock()->pauseAt($currentTime);

    // Wait a bit and run timers manually
    $page->clock()->runFor(3000); // 3 seconds

    $counter = (int) $page->evaluate('() => document.getElementById("counter").textContent');

    expect($counter)->toBe(3); // Should have ticked 3 times
});

it('can work with DateTimeInterface', function (): void {
    Route::get('/', fn (): string => '<div></div>');

    $page = visit('/');

    $dateTime = new DateTimeImmutable('2021-01-01T10:30:00Z');
    $page->clock()->setFixedTime($dateTime);

    $isoString = (string) $page->evaluate('() => new Date().toISOString()');

    expect($isoString)->toBe('2021-01-01T10:30:00.000Z');
});

it('can fast forward time', function (): void {
    Route::get('/', fn (): string => '
        <div id="result">waiting</div>
        <script>
            setTimeout(() => {
                document.getElementById("result").textContent = "done";
            }, 10000); // 10 seconds
        </script>
    ');

    $page = visit('/');

    $page->clock()->install();
    $page->reload();

    // Fast forward 15 seconds
    $page->clock()->fastForward(15000);

    $result = (string) $page->evaluate('() => document.getElementById("result").textContent');

    expect($result)->toBe('done');
});
