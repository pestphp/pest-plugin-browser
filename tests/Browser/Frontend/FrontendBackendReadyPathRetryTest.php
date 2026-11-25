<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Pest\Browser\Frontend\Frontend;
use Pest\Browser\Support\Port;
use Tests\Support\FrontendFixture;

use function Pest\Browser\frontend;

beforeEach(function (): void {
    $this->directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'pest-browser-ready-retry-frontend-'.getmypid();

    @mkdir($this->directory);

    file_put_contents($this->directory.DIRECTORY_SEPARATOR.'router.php', <<<'PHP'
        <?php

        $body = @file_get_contents(getenv('FIXTURE_BACKEND_URL').'/ready', false, stream_context_create(['http' => ['ignore_errors' => true]]));
        $status = preg_match('#^HTTP/\S+\s+(\d{3})#', http_get_last_response_headers()[0] ?? '', $matches) === 1 ? (int) $matches[1] : 0;

        if ($body === false || $status >= 500) {
            http_response_code(500);
        }

        echo $body;
        PHP);

    pest()->browser()
        ->frontend($this->directory, FrontendFixture::command(), Port::find())
        ->backendUrlEnv('FIXTURE_BACKEND_URL')
        ->bridgePort(Port::find())
        ->timeout(10);
});

afterEach(function (): void {
    $frontend = Frontend::instance();

    Frontend::reset();
    FrontendFixture::stopPort($frontend->port);
    FrontendFixture::stopBridge($frontend->bridge());

    @unlink($this->directory.DIRECTORY_SEPARATOR.'router.php');
    @rmdir($this->directory);
});

it('does not throw in the test the error of a ready probe that a later probe passed', function (): void {
    $attempts = 0;

    Route::get('/ready', function () use (&$attempts): string {
        if (++$attempts === 1) {
            throw new RuntimeException('backend still warming up');
        }

        return 'backend ready';
    });

    $this->withoutExceptionHandling();

    visit(frontend('/'))
        ->assertSee('backend ready');
});
