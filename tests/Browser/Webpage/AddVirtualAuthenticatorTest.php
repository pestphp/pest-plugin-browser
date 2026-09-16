<?php

declare(strict_types=1);

use Pest\Browser\Enums\BrowserType;
use Pest\Browser\Exceptions\BrowserNotSupportedException;
use Pest\Browser\Playwright\Playwright;

function definePasskeyRoute(): void
{
    Route::get('/', fn (): string => <<<'HTML'
        <button id="register">Register</button>
        <button id="sign-in">Sign in</button>
        <div id="status"></div>
        <script>
            document.getElementById('register').addEventListener('click', async () => {
                try {
                    await navigator.credentials.create({
                        publicKey: {
                            rp: {name: 'Pest'},
                            user: {
                                id: crypto.getRandomValues(new Uint8Array(16)),
                                name: 'pest@example.com',
                                displayName: 'Pest',
                            },
                            challenge: crypto.getRandomValues(new Uint8Array(32)),
                            pubKeyCredParams: [{type: 'public-key', alg: -7}],
                            authenticatorSelection: {
                                residentKey: 'required',
                                userVerification: 'required',
                            },
                        },
                    });
                    document.getElementById('status').textContent = 'Registered';
                } catch (error) {
                    document.getElementById('status').textContent = error.name;
                }
            });

            document.getElementById('sign-in').addEventListener('click', async () => {
                try {
                    await navigator.credentials.get({
                        publicKey: {
                            challenge: crypto.getRandomValues(new Uint8Array(32)),
                            userVerification: 'required',
                        },
                    });
                    document.getElementById('status').textContent = 'Signed in';
                } catch (error) {
                    document.getElementById('status').textContent = error.name;
                }
            });
        </script>
        HTML);
}

it('registers a passkey with a virtual authenticator', function (): void {
    definePasskeyRoute();

    $page = visit('/')->withHost('localhost');

    $authenticator = $page->addVirtualAuthenticator();

    $page->click('#register');
    $page->assertSee('Registered');

    $credentials = $authenticator->credentials();

    expect($credentials)->toHaveCount(1)
        ->and($credentials[0]['isResidentCredential'])->toBeTrue()
        ->and($credentials[0]['rpId'])->toBe('localhost');

    // Asserting again proves the call was not retried into a second authenticator.
    expect($authenticator->credentials())->toHaveCount(1);
});

it('signs in with a passkey it registered', function (): void {
    definePasskeyRoute();

    $page = visit('/')->withHost('localhost');

    $authenticator = $page->addVirtualAuthenticator();

    $page->click('#register');
    $page->assertSee('Registered');

    $page->click('#sign-in');
    $page->assertSee('Signed in');

    expect($authenticator->credentials()[0]['signCount'])->toBeGreaterThan(1);
});

it('fails the ceremony without a virtual authenticator', function (): void {
    definePasskeyRoute();

    $page = visit('/')->withHost('localhost');

    $page->click('#register');
    $page->assertSee('NotSupportedError');
});

it('starts with no credentials', function (): void {
    definePasskeyRoute();

    $page = visit('/')->withHost('localhost');

    $authenticator = $page->addVirtualAuthenticator();

    expect($authenticator->credentials())->toBe([]);
});

it('merges given options over the defaults', function (): void {
    definePasskeyRoute();

    $page = visit('/')->withHost('localhost');

    $page->addVirtualAuthenticator(['isUserVerified' => false]);

    $page->click('#register');
    $page->assertSee('NotAllowedError');
});

it('refuses to add a virtual authenticator outside of chrome', function (): void {
    definePasskeyRoute();

    $page = visit('/')->withHost('localhost');

    $page->assertSee('Register');

    Playwright::setDefaultBrowserType(BrowserType::FIREFOX);

    try {
        expect(fn (): Pest\Browser\Api\VirtualAuthenticator => $page->addVirtualAuthenticator())
            ->toThrow(BrowserNotSupportedException::class);
    } finally {
        Playwright::setDefaultBrowserType(BrowserType::CHROME);
    }
});
