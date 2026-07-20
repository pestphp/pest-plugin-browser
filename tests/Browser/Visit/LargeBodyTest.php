<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

it('accepts JSON request bodies larger than the default 128 KiB limit', function (): void {
    Route::post('/large-body/echo', fn (Request $request): array => [
        'received_bytes' => mb_strlen($request->getContent()),
        'received_count' => count($request->json('items', [])),
    ]);

    // Build a payload that comfortably exceeds Amp's 128 KiB default.
    $items = [];
    for ($i = 0; $i < 5000; $i++) {
        $items[] = [
            'id' => $i,
            'name' => str_repeat('x', 32),
            'description' => str_repeat('y', 64),
        ];
    }
    $payload = json_encode(['items' => $items]);

    expect(mb_strlen($payload))->toBeGreaterThan(128 * 1024);

    // Render a tiny page that POSTs the payload via fetch and writes the JSON
    // response into the DOM so we can assert against it.
    Route::get('/large-body/post-from-page', fn () => '
        <html><body>
            <pre id="result"></pre>
            <script>
                fetch("/large-body/echo", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: '.json_encode($payload).',
                })
                .then(r => r.json())
                .then(data => { document.getElementById("result").textContent = JSON.stringify(data); });
            </script>
        </body></html>
    ');

    visit('/large-body/post-from-page')
        ->assertSee('"received_bytes":'.mb_strlen($payload))
        ->assertSee('"received_count":5000');
});
