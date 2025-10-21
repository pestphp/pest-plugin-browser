<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

test('may set geolocation', function (): void {
    Route::get('/', fn (): string => '
        <html>
        <head></head>
        <body>
            <div>Location:</div>
            <div id="coordinates">
                <span id="latitude">Waiting...</span>
                <span id="longitude">Waiting...</span>
            </div>
            <script>
                navigator.geolocation.getCurrentPosition(function(position) {
                    document.getElementById("latitude").textContent = position.coords.latitude;
                    document.getElementById("longitude").textContent = position.coords.longitude;
                });
            </script>
        </body>
        </html>
    ');

    $latitude = 51.5074;
    $longitude = -0.1278;

    visit('/')
        ->geolocation($latitude, $longitude)
        ->assertSeeIn('#latitude', (string) $latitude)
        ->assertSeeIn('#longitude', (string) $longitude)
        ->assertDontSee('Waiting...');
});
