<?php

declare(strict_types=1);

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

it('may wait for function to return true', function (): void {
    Route::get('/', fn (): string => '
        <div id="content">Loading...</div>
        <script>
            setTimeout(() => {
                document.getElementById("content").textContent = "Loaded!";
            }, 100);
        </script>
    ');

    $page = visit('/');

    $page->waitForFunction('() => document.getElementById("content").textContent === "Loaded!"');

    $page->assertSeeIn('#content', 'Loaded!');
});

it('may wait for url', function (): void {
    Route::get('/page-a', function (): RedirectResponse {
        sleep(1);

        return redirect('/page-b');
    });

    Route::get('/page-b', fn (): string => 'page 2');

    $page = visit('/page-a');
    $page->waitForURL('/page-b');
    $page->assertSee('page 2');
});

it('may wait for selector', function (): void {
    Route::get('/', fn (): string => '
        <div id="container"></div>
        <script>
            setTimeout(() => {
                const container = document.getElementById("container");
                const childElement = document.createElement("div");
                childElement.id = "child";
                childElement.textContent = "Child Element Added";
                container.appendChild(childElement);
            }, 100);
        </script>
    ');

    $page = visit('/');

    $page->waitForSelector('#child');

    $page->assertSeeIn('#child', 'Child Element Added');
});
