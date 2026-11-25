<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Pest\Browser\Frontend\Frontend;
use Pest\Browser\Support\Port;
use Tests\Support\FrontendFixture;

beforeEach(function (): void {
    $this->directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'pest-browser-ready-frontend-'.getmypid();

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
        ->timeout(5);

    Route::get('/ready', fn (): string => 'backend ready');
});

afterEach(function (): void {
    $frontend = Frontend::instance();

    Frontend::reset();
    FrontendFixture::stopPort($frontend->port);
    FrontendFixture::stopBridge($frontend->bridge());

    @unlink($this->directory.DIRECTORY_SEPARATOR.'router.php');
    @rmdir($this->directory);
});

it('starts a frontend whose ready path renders data of the backend', function (): void {
    expect(Frontend::instance()->url('/'))->toStartWith('http://');
});
