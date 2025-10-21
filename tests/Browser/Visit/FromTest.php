<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Pest\Browser\Enums\City;

it('can emulate being from another location', function (): void {
    Route::get('/', fn (): string => '
        <html>
        <head></head>
        <body>
            <div>Location:</div>
            <div id="data">
                <span id="timezone">Waiting...</span>
                <span id="locale">Waiting...</span>
                <span id="latitude">Waiting...</span>
                <span id="longitude">Waiting...</span>
            </div>
            <script>
                    document.getElementById("timezone").textContent = Intl.DateTimeFormat().resolvedOptions().timeZone;
                    document.getElementById("locale").textContent = navigator.languages[0];
                    navigator.geolocation.getCurrentPosition(function(position) {
                        document.getElementById("latitude").textContent = position.coords.latitude;
                        document.getElementById("longitude").textContent = position.coords.longitude;
                    });
            </script>
        </body>
        </html>
    ');

    $cities = City::cases();

    foreach ($cities as $city) {
        visit('/')
            ->from()->{$city->value}()
            ->assertSeeIn('#timezone', $city->timezone())
            ->assertSeeIn('#locale', $city->locale())
            ->assertSeeIn('#latitude', (string) $city->geolocation()['latitude'])
            ->assertSeeIn('#longitude', (string) $city->geolocation()['longitude'])
            ->assertDontSee('Waiting...');
    }
});
