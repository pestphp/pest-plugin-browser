<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\StreamedResponse;

it('may serve a binary streamed response without corrupting its bytes', function (): void {
    $bytes = "\x20\x0A\xFF\xD8\xFF\xE0PAYLOAD-1234567890\xFF\x00\x0A\x20";

    Route::get('/', fn (): string => '<div>Home</div>');
    Route::get('/binary', fn (): StreamedResponse => response()->stream(
        function () use ($bytes): void {
            echo $bytes;
        },
        200,
        ['Content-Type' => 'image/jpeg'],
    ));

    $page = visit('/');

    $page->assertScript(
        "async () => {
            const response = await fetch('/binary');
            const bytes = new Uint8Array(await response.arrayBuffer());

            return Array.from(bytes).join(',');
        }",
        implode(',', unpack('C*', $bytes)),
    );
});

it('may serve a binary streamed image that the browser is able to decode', function (): void {
    $image = file_get_contents(__DIR__.'/../../Fixtures/v4.jpg');

    Route::get('/', fn (): string => '<img id="image" src="/image" alt="Streamed Image">');
    Route::get('/image', fn (): StreamedResponse => response()->stream(
        function () use ($image): void {
            echo $image;
        },
        200,
        ['Content-Type' => 'image/jpeg'],
    ));

    $page = visit('/');

    $page->assertScript("document.getElementById('image').complete && document.getElementById('image').naturalWidth > 0");
});

it('may serve a textual streamed response without altering its whitespace', function (): void {
    Route::get('/', fn (): string => '<div>Home</div>');
    Route::get('/text', fn (): StreamedResponse => response()->stream(
        function (): void {
            echo "\n  Hello World  \n";
        },
        200,
        ['Content-Type' => 'text/plain'],
    ));

    $page = visit('/');

    $page->assertScript(
        "async () => JSON.stringify(await (await fetch('/text')).text())",
        json_encode("\n  Hello World  \n"),
    );
});

it('delivers every server-sent event to the browser', function (): void {
    Route::get('/', fn (): string => '<div id="received">-</div>
        <script>
            window.events = [];

            new EventSource("/events").onmessage = (event) => {
                window.events.push(event.data);
                document.getElementById("received").textContent = window.events.join(",");
            };
        </script>');
    Route::get('/events', fn (): StreamedResponse => response()->stream(
        function (): void {
            echo "data: first\n\n";
            echo "data: second\n\n";
        },
        200,
        ['Content-Type' => 'text/event-stream', 'Cache-Control' => 'no-cache'],
    ));

    $page = visit('/');

    $page->assertSee('first');

    expect($page->text('#received'))->toBe('first,second');
});
