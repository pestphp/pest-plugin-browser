<?php

declare(strict_types=1);

use Pest\Browser\Support\BrowserTestIdentifier;

/**
 * Helper to call the private usesFunction method via reflection.
 */
function callUsesFunction(Closure $closure, string $functionName): bool
{
    $method = new ReflectionMethod(BrowserTestIdentifier::class, 'usesFunction');

    return $method->invoke(null, $closure, $functionName);
}

// visit detection — unqualified calls

it('detects visit()', function (): void {
    $closure = function (): void {
        visit('/');
    };

    expect(callUsesFunction($closure, 'visit'))->toBeTrue();
});

it('detects visit() with a URL argument', function (): void {
    $closure = function (): void {
        visit('https://example.com');
    };

    expect(callUsesFunction($closure, 'visit'))->toBeTrue();
});

it('detects Visit() case-insensitively', function (): void {
    $closure = function (): void {
        Visit('/');
    };

    expect(callUsesFunction($closure, 'visit'))->toBeTrue();
});

// visit detection — fully qualified calls

it('detects \visit()', function (): void {
    $closure = function (): void {
        \visit('/');
    };

    expect(callUsesFunction($closure, 'visit'))->toBeTrue();
});

it('detects \visit() with a URL argument', function (): void {
    $closure = function (): void {
        \visit('https://example.com');
    };

    expect(callUsesFunction($closure, 'visit'))->toBeTrue();
});

// visit detection — Livewire

it('detects Livewire::visit()', function (): void {
    $closure = function (): void {
        Livewire::visit('/');
    };

    expect(callUsesFunction($closure, 'visit'))->toBeTrue();
});

// visit detection — negative cases

it('does not detect visit as a method call', function (): void {
    $closure = function (): void {
        $page->visit('/');
    };

    expect(callUsesFunction($closure, 'visit'))->toBeFalse();
});

it('does not detect visit inside a string', function (): void {
    $closure = function (): void {
        $x = 'visit()';
    };

    expect(callUsesFunction($closure, 'visit'))->toBeFalse();
});

it('does not detect visit when not called as a function', function (): void {
    $closure = function (): void {
        $visit = true;
    };

    expect(callUsesFunction($closure, 'visit'))->toBeFalse();
});

it('does not detect a closure without visit', function (): void {
    $closure = function (): void {
        $x = 1 + 2;
    };

    expect(callUsesFunction($closure, 'visit'))->toBeFalse();
});

// debug detection

it('detects debug()', function (): void {
    $closure = function (): void {
        debug();
    };

    expect(callUsesFunction($closure, 'debug'))->toBeTrue();
});

it('detects \debug()', function (): void {
    $closure = function (): void {
        \debug();
    };

    expect(callUsesFunction($closure, 'debug'))->toBeTrue();
});

it('does not detect debug when not present', function (): void {
    $closure = function (): void {
        visit('/');
    };

    expect(callUsesFunction($closure, 'debug'))->toBeFalse();
});
