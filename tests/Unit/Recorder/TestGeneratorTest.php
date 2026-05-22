<?php

declare(strict_types=1);

use Pest\Browser\Recorder\RecordedEvent;
use Pest\Browser\Recorder\TestGenerator;

function navigate(string $url): RecordedEvent
{
    return new RecordedEvent(type: 'navigate', url: $url);
}

function click(string $name): RecordedEvent
{
    return new RecordedEvent(
        type: 'click',
        locator: [
            'kind' => 'role',
            'body' => 'button',
            'options' => ['name' => $name],
        ],
    );
}

function fill(string $id, string $value): RecordedEvent
{
    return new RecordedEvent(
        type: 'fill',
        locator: [
            'kind' => 'test-id',
            'body' => $id,
            'options' => [],
        ],
        text: $value,
    );
}

function assertText(string $text): RecordedEvent
{
    return new RecordedEvent(type: 'assertText', text: $text);
}

// actingAs
it('injects actingAs when actingAs=true', function (): void {
    $generator = new TestGenerator('id');
    $events = [navigate('http://localhost/'), fill('email', 'test@example.com')];
    $code = $generator->generate($events, 'can do something', 'http://localhost', true);

    expect($code)->toContain('$this->actingAs(\App\Models\User::factory()->create())');
});

it('does not inject actingAs when actingAs=false', function (): void {
    $generator = new TestGenerator('id');
    $events = [navigate('http://localhost/'), fill('email', 'test@example.com')];
    $code = $generator->generate($events, 'can do something', 'http://localhost', false);

    expect($code)->not->toContain('actingAs');
});

// test structure
it('wraps output in it() closure', function (): void {
    $generator = new TestGenerator('id');
    $events = [navigate('http://localhost/')];
    $code = $generator->generate($events, 'can visit home', 'http://localhost');

    expect($code)->toStartWith("it('can visit home', function (): void {");
});

it('escapes single quotes in title', function (): void {
    $generator = new TestGenerator('id');
    $events = [navigate('http://localhost/')];
    $code = $generator->generate($events, "can't login", 'http://localhost');

    expect($code)->toContain("it('can\\'t login'");
});

// navigation grouping
it('groups events into visit() blocks per page', function (): void {
    $generator = new TestGenerator('id');
    $events = [
        navigate('http://localhost/'),
        click('Submit'),
        navigate('http://localhost/dashboard'),
        click('Settings'),
    ];
    $code = $generator->generate($events, 'can navigate', 'http://localhost');

    expect($code)
        ->toContain("visit('/')")
        ->toContain("visit('/dashboard')");
});

it('strips base url from path', function (): void {
    $generator = new TestGenerator('id');
    $events = [navigate('http://localhost:8000/dashboard')];
    $code = $generator->generate($events, 'test', 'http://localhost:8000');

    expect($code)->toContain("visit('/dashboard')");
});

it('uses / when url matches base exactly', function (): void {
    $generator = new TestGenerator('id');
    $events = [navigate('http://localhost/')];
    $code = $generator->generate($events, 'test', 'http://localhost');

    expect($code)->toContain("visit('/')");
});

// action rendering
it('renders click action', function (): void {
    $generator = new TestGenerator('id');
    $events = [navigate('http://localhost/'), click('Log in')];
    $code = $generator->generate($events, 'test', 'http://localhost');

    expect($code)->toContain("->click('Log in')");
});

it('renders fill action with id selector', function (): void {
    $generator = new TestGenerator('id');
    $events = [navigate('http://localhost/'), fill('email', 'user@example.com')];
    $code = $generator->generate($events, 'test', 'http://localhost');

    expect($code)->toContain("->fill('#email', 'user@example.com')");
});

it('renders assertText as assertSee without selector', function (): void {
    $generator = new TestGenerator('id');
    $events = [navigate('http://localhost/'), assertText('These credentials do not match')];
    $code = $generator->generate($events, 'test', 'http://localhost');

    expect($code)
        ->toContain("->assertSee('These credentials do not match')")
        ->not->toContain('assertSeeIn');
});

it('skips assertText with empty text', function (): void {
    $generator = new TestGenerator('id');
    $events = [navigate('http://localhost/'), assertText('')];
    $code = $generator->generate($events, 'test', 'http://localhost');

    expect($code)->not->toContain('assertSee');
});

// escaping
it('escapes single quotes in fill value', function (): void {
    $generator = new TestGenerator('id');
    $events = [navigate('http://localhost/'), fill('name', "O'Brien")];
    $code = $generator->generate($events, 'test', 'http://localhost');

    expect($code)->toContain("->fill('#name', 'O\\'Brien')");
});

it('escapes backslashes in fill value', function (): void {
    $generator = new TestGenerator('id');
    $events = [navigate('http://localhost/'), fill('path', 'C:\\Users')];
    $code = $generator->generate($events, 'test', 'http://localhost');

    expect($code)->toContain("->fill('#path', 'C:\\\\Users')");
});

// multiple pages separated by blank line
it('separates multiple pages with blank line', function (): void {
    $generator = new TestGenerator('id');
    $events = [
        navigate('http://localhost/'),
        navigate('http://localhost/dashboard'),
    ];
    $code = $generator->generate($events, 'test', 'http://localhost');

    expect($code)->toContain("\n\n");
});

// actingAs placed before first visit
it('places actingAs before visit when actingAs=true', function (): void {
    $generator = new TestGenerator('id');
    $events = [navigate('http://localhost/dashboard')];
    $code = $generator->generate($events, 'test', 'http://localhost', true);
    $actingAsPos = strpos($code, 'actingAs');
    $visitPos = strpos($code, 'visit(');

    expect($actingAsPos)->toBeLessThan($visitPos);
});
