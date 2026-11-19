<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Pest\Browser\Api\AwaitableWebpage;
use PHPUnit\Framework\ExpectationFailedException;
use Tests\Support\ExternalSite;

it('can interact with iframe content using withinFrame', function (): void {
    $iframeContent = <<<'HTML'
        <!DOCTYPE html>
        <html>
        <head><title>Frame Content</title></head>
        <body>
            <h2>Inside Iframe</h2>
            <input type="text" id="frame-input" placeholder="Type here" />
            <button id="frame-button">Click me</button>
            <div id="result"></div>
        </body>
        </html>
        HTML;

    $mainContent = <<<'HTML'
        <!DOCTYPE html>
        <html>
        <head><title>Main Page</title></head>
        <body>
            <h1>Main Page</h1>
            <div class="iframe-container">
                <iframe id="test-iframe" src="/iframe" width="400" height="300"></iframe>
            </div>
        </body>
        </html>
        HTML;

    Route::get('/', fn (): string => $mainContent);
    Route::get('/iframe', fn (): string => $iframeContent);

    $page = visit('/');
    $page->assertSee('Main Page');

    $page->withinFrame('.iframe-container', function (AwaitableWebpage $frame): void {
        $frame->assertSee('Inside Iframe')
            ->type('frame-input', 'Hello iframe')
            ->click('frame-button');
    });
});

it('can interact with iframe that uses JavaScript', function (): void {
    $iframeContent = <<<'HTML'
        <!DOCTYPE html>
        <html>
        <head><title>Frame Content</title></head>
        <body>
            <h2>Inside Iframe</h2>
            <input type="text" id="frame-input" placeholder="Type here" />
            <button id="frame-button" onclick="handleClick()">Click me</button>
            <div id="result"></div>
            <script>
                function handleClick() {
                    var input = document.getElementById('frame-input');
                    var result = document.getElementById('result');
                    result.textContent = 'Clicked with: ' + input.value;
                }
            </script>
        </body>
        </html>
        HTML;

    $mainContent = <<<'HTML'
        <!DOCTYPE html>
        <html>
        <head><title>Main Page</title></head>
        <body>
            <h1>Main Page</h1>
            <div class="iframe-wrapper">
                <iframe id="test-iframe" src="/iframe" width="500" height="400"></iframe>
            </div>
        </body>
        </html>
        HTML;

    Route::get('/', fn (): string => $mainContent);
    Route::get('/iframe', fn (): string => $iframeContent);

    $page = visit('/');
    $page->assertSee('Main Page');

    $page->withinFrame('.iframe-wrapper', function (AwaitableWebpage $frame): void {
        $frame->assertSee('Inside Iframe')
            ->type('frame-input', 'Hello from JavaScript')
            ->click('frame-button')
            ->assertSee('Clicked with: Hello from JavaScript');
    });
});

it('can interact with iframe from external URL content', function (): void {
    ExternalSite::run(function (string $url): void {
        $mainContent = <<<'HTML'
            <!DOCTYPE html>
            <html>
            <head>
                <title>Cross-Origin Iframe Test</title>
            </head>
            <body>
                <h1>Cross-Origin Iframe Behavior Test</h1>

                <div class="iframe-container">
                    <iframe id="external-iframe" src="__EXTERNAL_URL__"></iframe>
                </div>
            </body>
            </html>
            HTML;

        Route::get('/', fn (): string => str_replace('__EXTERNAL_URL__', $url, $mainContent));

        $page = visit('/')->assertSee('Cross-Origin Iframe Behavior Test');

        expect($page->script('window.location.origin'))->not->toBe($url);

        $page->withinFrame('.iframe-container', function (AwaitableWebpage $frame) use ($url): void {
            $frame->assertSee('External fixture page');

            expect($frame->script('window.location.origin'))->toBe($url);
        });
    });
});

it('can interact with two iframes from external URL content', function (): void {
    ExternalSite::run(function (string $url): void {
        $mainContent = <<<'HTML'
            <!DOCTYPE html>
            <html>
            <head>
                <title>Cross-Origin Iframe Test</title>
            </head>
            <body>
                <h1>Cross-Origin Iframe Behavior Test</h1>

                <div class="iframe-container">
                    <iframe id="external-iframe" src="__EXTERNAL_URL__"></iframe>
                </div>

                <div class="another-iframe-container">
                    <iframe id="external-iframe" src="__EXTERNAL_URL__"></iframe>
                </div>
            </body>
            </html>
            HTML;

        Route::get('/', fn (): string => str_replace('__EXTERNAL_URL__', $url, $mainContent));

        $page = visit('/')->assertSee('Cross-Origin Iframe Behavior Test');

        expect($page->script('window.location.origin'))->not->toBe($url);

        $page->withinFrame('.iframe-container', function (AwaitableWebpage $frame) use ($url): void {
            $frame->assertSee('External fixture page');

            expect($frame->script('window.location.origin'))->toBe($url);
        });

        $page->withinFrame('.another-iframe-container', function (AwaitableWebpage $frame) use ($url): void {
            $frame->assertSee('External fixture page');

            expect($frame->script('window.location.origin'))->toBe($url);
        });
    });
});

it('waits for an iframe that is inserted after the page has loaded', function (string $origin): void {
    Route::get('/iframe', fn (): string => '<html><body><input type="text" name="number" /></body></html>');

    Route::get('/', fn (): string => <<<HTML
        <!DOCTYPE html>
        <html>
        <head><title>Main Page</title></head>
        <body>
            <h1>Main Page</h1>
            <script>
                setTimeout(() => {
                    const iframe = document.createElement('iframe');
                    iframe.title = 'Embedded form';
                    iframe.src = {$origin} + '/iframe';
                    document.body.appendChild(iframe);
                }, 500);
            </script>
        </body>
        </html>
        HTML);

    visit('/')->withinFrame('iframe[title="Embedded form"]', function (AwaitableWebpage $frame): void {
        $frame->fill('[name=number]', '4242')
            ->assertValue('[name=number]', '4242');
    });
})->with([
    'same origin' => 'location.origin',
    'cross origin' => "'http://localhost:' + location.port",
]);

it('waits for an iframe while the page keeps the network busy', function (): void {
    Route::get('/iframe', fn (): string => '<html><body><input type="text" name="number" /></body></html>');
    Route::get('/ping', fn (): string => 'pong');

    Route::get('/', fn (): string => <<<'HTML'
        <!DOCTYPE html>
        <html>
        <head><title>Main Page</title></head>
        <body>
            <h1>Main Page</h1>
            <script>
                setInterval(() => fetch('/ping'), 100);

                setTimeout(() => {
                    const iframe = document.createElement('iframe');
                    iframe.title = 'Embedded form';
                    iframe.src = '/iframe';
                    document.body.appendChild(iframe);
                }, 500);
            </script>
        </body>
        </html>
        HTML);

    visit('/')->withinFrame('iframe[title="Embedded form"]', function (AwaitableWebpage $frame): void {
        $frame->fill('[name=number]', '4242')
            ->assertValue('[name=number]', '4242');
    });
});

it('waits for a container and its iframe that are inserted after the page has loaded', function (string $script): void {
    Route::get('/iframe', fn (): string => '<html><body><input type="text" name="number" /></body></html>');

    Route::get('/', fn (): string => <<<HTML
        <!DOCTYPE html>
        <html>
        <head><title>Main Page</title></head>
        <body>
            <h1>Main Page</h1>
            {$script}
        </body>
        </html>
        HTML);

    visit('/')->withinFrame('.iframe-container', function (AwaitableWebpage $frame): void {
        $frame->fill('[name=number]', '4242')
            ->assertValue('[name=number]', '4242');
    });
})->with([
    'iframe inserted into an existing container' => <<<'HTML'
        <div class="iframe-container"></div>
        <script>
            setTimeout(() => {
                const iframe = document.createElement('iframe');
                iframe.src = '/iframe';
                document.querySelector('.iframe-container').appendChild(iframe);
            }, 500);
        </script>
        HTML,
    'container and iframe inserted together' => <<<'HTML'
        <script>
            setTimeout(() => {
                document.body.insertAdjacentHTML('beforeend', '<div class="iframe-container"><iframe src="/iframe"></iframe></div>');
            }, 500);
        </script>
        HTML,
]);

it('waits for nested iframes that are inserted after their pages have loaded', function (): void {
    Route::get('/inner', fn (): string => '<html><body><input type="text" name="number" /></body></html>');

    $insert = fn (string $title, string $src): string => <<<HTML
        <script>
            setTimeout(() => {
                const iframe = document.createElement('iframe');
                iframe.title = '{$title}';
                iframe.src = '{$src}';
                document.body.appendChild(iframe);
            }, 500);
        </script>
        HTML;

    Route::get('/outer', fn (): string => '<html><body><h2>Outer Frame</h2>'.$insert('Inner', '/inner').'</body></html>');
    Route::get('/', fn (): string => '<html><body><h1>Main Page</h1>'.$insert('Outer', '/outer').'</body></html>');

    visit('/')->withinFrame('iframe[title="Outer"]', function (AwaitableWebpage $outer): void {
        $outer->assertSee('Outer Frame')
            ->withinFrame('iframe[title="Inner"]', function (AwaitableWebpage $inner): void {
                $inner->fill('number', '4242')
                    ->assertValue('number', '4242');
            });
    });
});

it('fails with a strict mode violation when the selector matches several iframes', function (): void {
    Route::get('/iframe', fn (): string => '<html><body><h2>Inside Iframe</h2></body></html>');

    Route::get('/', fn (): string => <<<'HTML'
        <!DOCTYPE html>
        <html>
        <head><title>Main Page</title></head>
        <body>
            <h1>Main Page</h1>
            <iframe class="payment" src="/iframe"></iframe>
            <iframe class="payment" src="/iframe"></iframe>
        </body>
        </html>
        HTML);

    visit('/')->withinFrame('iframe.payment', function (AwaitableWebpage $frame): void {
        $frame->assertSee('Inside Iframe');
    });
})->throws(ExpectationFailedException::class, 'strict mode violation');
