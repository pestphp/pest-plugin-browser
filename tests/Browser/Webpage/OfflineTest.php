<?php

declare(strict_types=1);

it('may take the browser offline and back online', function (): void {
    Route::get('/', fn (): string => '
        <div id="status">idle</div>
        <button id="ping">Ping</button>
        <script>
            document.getElementById("ping").addEventListener("click", function () {
                var status = document.getElementById("status");

                status.textContent = "pending";

                fetch("/ping")
                    .then(function () { status.textContent = "reachable"; })
                    .catch(function () { status.textContent = "unreachable"; });
            });
        </script>
    ');

    Route::get('/ping', fn (): string => 'pong');

    visit('/')
        ->offline()
        ->click('#ping')
        ->assertSeeIn('#status', 'unreachable')
        ->online()
        ->click('#ping')
        ->assertSeeIn('#status', 'reachable');
});

it('may reflect the offline state on the navigator', function (): void {
    Route::get('/', fn (): string => '
        <div id="connection">unknown</div>
        <script>
            function render() {
                document.getElementById("connection").textContent =
                    navigator.onLine ? "online" : "offline";
            }

            window.addEventListener("online", render);
            window.addEventListener("offline", render);

            render();
        </script>
    ');

    visit('/')
        ->assertSeeIn('#connection', 'online')
        ->offline()
        ->assertSeeIn('#connection', 'offline')
        ->online()
        ->assertSeeIn('#connection', 'online');
});
