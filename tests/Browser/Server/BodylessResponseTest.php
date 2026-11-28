<?php

declare(strict_types=1);

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

it('serves the next request on a keep-alive connection after a body-less response', function (int $status): void {
    Route::post('/beacon', fn (): Response => new Response('', $status));

    Route::get('/after-beacon.js', fn (): Response => new Response('window.afterBeaconLoaded = true;', 200, [
        'Content-Type' => 'text/javascript',
    ]));

    Route::get('/', fn (): string => <<<'HTML'
        <html>
        <body>
        <script>
            fetch('/beacon', {method: 'POST', keepalive: true}).then(() => {
                const script = document.createElement('script');
                script.src = '/after-beacon.js';
                script.onerror = () => { window.afterBeaconFailed = true; };
                document.body.appendChild(script);
            });
        </script>
        </body>
        </html>
        HTML);

    visit('/')->assertScript('window.afterBeaconLoaded === true || window.afterBeaconFailed === true');

    visit('/')->assertScript('window.afterBeaconLoaded === true');
})->with([204, 304])->repeat(3);
