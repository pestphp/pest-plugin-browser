<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Pest\Browser\Api\AwaitableWebpage;
use Pest\Browser\Exceptions\MethodAlreadyExistsException;
use Pest\Browser\Playwright\Page;
use Pest\Browser\Playwright\Tracing;
use PHPUnit\Framework\ExpectationFailedException;

it('may call a registered extension on a visited page', function (): void {
    Route::get('/', fn (): string => '<h1 id="title">Welcome</h1>');

    pest()->browser()->extend('assertWelcomed', function (): void {
        $this->assertSee('Welcome');
    });

    visit('/')->assertWelcomed();
});

it('keeps the chain going when the extension returns nothing', function (): void {
    Route::get('/', fn (): string => '<a href="/about">About Us</a>');
    Route::get('/about', fn (): string => 'About Page');

    pest()->browser()->extend('goToAbout', function (): void {
        $this->click('About Us');
    });

    visit('/')
        ->goToAbout()
        ->assertUrlIs(url('/about'))
        ->assertSee('About Page');
});

it('forwards arguments and return values', function (): void {
    Route::get('/', fn (): string => '<input id="name" value="Taylor">');

    pest()->browser()->extend('valueOf', fn (string $selector): string => $this->value($selector));

    expect(visit('/')->valueOf('#name'))->toBe('Taylor');
});

it('keeps the chain going when an extension without a return type returns nothing', function (): void {
    Route::get('/', fn (): string => '<a href="/about">About Us</a>');
    Route::get('/about', fn (): string => 'About Page');

    pest()->browser()->extend('goToAboutUntyped', require __DIR__.'/../../Fixtures/untyped-extend.php');

    visit('/')
        ->goToAboutUntyped()
        ->assertSee('About Page');
});

it('returns null from an extension that declares a nullable return type', function (): void {
    Route::get('/', fn (): string => '<a id="home">Home</a>');

    pest()->browser()->extend('hrefOf', fn (string $selector): ?string => $this->attribute($selector, 'href'));

    expect(visit('/')->hrefOf('#home'))->toBeNull();
});

it('binds the extension to the awaitable webpage', function (): void {
    Route::get('/', fn (): string => 'Home');

    pest()->browser()->extend('exposeThis', fn (): array => [$this, $this->page]);

    $page = visit('/');

    [$bound, $playwrightPage] = $page->exposeThis();

    expect($bound)->toBeInstanceOf(AwaitableWebpage::class)
        ->and($playwrightPage)->toBeInstanceOf(Page::class);
});

it('may call one extension from another', function (): void {
    Route::get('/', fn (): string => '<h1 id="title">Welcome</h1>');

    pest()->browser()
        ->extend('assertHeading', function (string $text): void {
            $this->assertSeeIn('#title', $text);
        })
        ->extend('assertWelcomeHeading', function (): void {
            $this->assertHeading('Welcome');
        });

    visit('/')->assertWelcomeHeading();
});

it('may call an extension on several pages at once', function (): void {
    Route::get('/one', fn (): string => '<h1 id="title">Page</h1>');
    Route::get('/two', fn (): string => '<h1 id="title">Page</h1>');

    pest()->browser()->extend('assertPageHeading', function (): void {
        $this->assertSeeIn('#title', 'Page');
    });

    visit(['/one', '/two'])->assertPageHeading();
});

it('refuses to register an extension named after a built-in method', function (string $name): void {
    pest()->browser()->extend($name, function (): void {
        //
    });
})->with(['click', 'page', 'withinFrame', 'on', 'count'])
    ->throws(MethodAlreadyExistsException::class, 'a method with that name already exists');

it('does not replay an extension after its clicks have run', function (): void {
    Route::get('/', fn (): string => '
        <script>window.clicks = 0;</script>
        <button id="save" onclick="window.clicks++">Save</button>
    ');

    $attempts = 0;

    pest()->browser()->extend('saveThenFail', function () use (&$attempts): void {
        $attempts++;
        $this->click('Save');

        expect(false)->toBeTrue('the extension fails after the click');
    });

    $page = visit('/');

    expect(fn () => $page->saveThenFail())->toThrow(ExpectationFailedException::class);

    expect($attempts)->toBe(1);
    $page->assertScript('window.clicks', 1);
});

it('groups the steps of an extension under its name in the trace', function (): void {
    Route::get('/', fn (): string => '<h1 id="title">Welcome</h1>');

    pest()->browser()->extend('assertTracedWelcome', function (): void {
        $this->assertSee('Welcome');
    });

    $page = visit('/')->assertSee('Welcome');

    $page->page()->context()->tracing()?->start('extend-traced');

    $page->assertTracedWelcome();

    $paths = Tracing::stopAll(save: true);

    $zip = new ZipArchive();
    $zip->open($paths[0]);

    $events = array_map(
        fn (string $line): mixed => json_decode($line, true),
        explode("\n", mb_trim((string) $zip->getFromName('trace.trace'))),
    );

    $zip->close();

    unlink($paths[0]);

    $groups = array_values(array_filter(
        $events,
        fn (mixed $event): bool => is_array($event) && ($event['method'] ?? null) === 'tracingGroup',
    ));

    expect($groups)->toHaveCount(2)
        ->and($groups[0]['title'])->toBe('assertTracedWelcome()')
        ->and($groups[0]['stack'][0]['file'])->toBe(__FILE__)
        ->and($groups[1]['title'])->toBe("assertSee('Welcome')")
        ->and($groups[1]['parentId'])->toBe($groups[0]['callId']);
});
