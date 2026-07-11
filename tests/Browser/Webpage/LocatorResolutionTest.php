<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\ExpectationFailedException;

it('waits for a delayed field instead of falling back to visible text and executes actions once', function (): void {
    Route::get('/', fn (): string => <<<'HTML'
        <div>email</div>
        <button type="button" id="reveal-field">Reveal field</button>
        <div id="field-container"></div>

        <script>
            window.revealFieldClicks = 0;
            window.emailInputEvents = 0;

            document.getElementById('reveal-field').addEventListener('click', () => {
                window.revealFieldClicks += 1;

                setTimeout(() => {
                    document.getElementById('field-container').innerHTML = `
                        <label for="email-field">Email address</label>
                        <input
                            id="email-field"
                            name="email"
                            placeholder="Enter email"
                            type="text"
                        >
                    `;

                    document
                        .getElementById('email-field')
                        .addEventListener('input', () => window.emailInputEvents += 1);
                }, 150);
            });
        </script>
        HTML);

    $page = visit('/');

    $page
        ->click('Reveal field')
        ->fill('email', 'tony@example.com')
        ->assertValue('email', 'tony@example.com');

    expect($page->script('() => window.revealFieldClicks'))->toBe(1)
        ->and($page->script('() => window.emailInputEvents'))->toBe(1);
});

it('waits for delayed field resolution by exact label and placeholder', function (string $selector): void {
    Route::get('/', fn (): string => <<<'HTML'
        <div>Email address</div>
        <div>Enter email</div>
        <button type="button" id="reveal-field">Reveal field</button>
        <div id="field-container"></div>

        <script>
            document.getElementById('reveal-field').addEventListener('click', () => {
                setTimeout(() => {
                    document.getElementById('field-container').innerHTML = `
                        <label for="email-field">Email address</label>
                        <input
                            id="email-field"
                            name="email"
                            placeholder="Enter email"
                            type="text"
                        >
                    `;
                }, 150);
            });
        </script>
        HTML);

    $page = visit('/');

    $page
        ->click('Reveal field')
        ->fill($selector, 'tony@example.com')
        ->assertValue('#email-field', 'tony@example.com');
})->with([
    'label' => 'Email address',
    'placeholder' => 'Enter email',
]);

it('waits for a delayed checkbox instead of falling back to visible text', function (): void {
    Route::get('/', fn (): string => <<<'HTML'
        <div>terms</div>
        <button type="button" id="reveal-checkbox">Reveal checkbox</button>
        <div id="checkbox-container"></div>

        <script>
            window.revealCheckboxClicks = 0;
            window.checkboxChanges = 0;

            document.getElementById('reveal-checkbox').addEventListener('click', () => {
                window.revealCheckboxClicks += 1;

                setTimeout(() => {
                    document.getElementById('checkbox-container').innerHTML = `
                        <input type="checkbox" id="terms-box" name="terms">
                        <label for="terms-box">Accept terms</label>
                    `;

                    document
                        .getElementById('terms-box')
                        .addEventListener('change', () => window.checkboxChanges += 1);
                }, 150);
            });
        </script>
        HTML);

    $page = visit('/');

    $page->click('Reveal checkbox')->check('terms');

    expect($page->script('() => document.getElementById("terms-box").checked'))->toBeTrue()
        ->and($page->script('() => window.revealCheckboxClicks'))->toBe(1)
        ->and($page->script('() => window.checkboxChanges'))->toBe(1);

    $page->uncheck('terms');

    expect($page->script('() => document.getElementById("terms-box").checked'))->toBeFalse()
        ->and($page->script('() => window.checkboxChanges'))->toBe(2);
});

it('waits for delayed radio controls and respects the requested value', function (): void {
    Route::get('/', fn (): string => <<<'HTML'
        <div>contact_method</div>
        <button type="button" id="reveal-radios">Reveal radios</button>
        <div id="radio-container"></div>

        <script>
            document.getElementById('reveal-radios').addEventListener('click', () => {
                setTimeout(() => {
                    document.getElementById('radio-container').innerHTML = `
                        <input type="radio" id="contact-phone" name="contact_method" value="phone">
                        <label for="contact-phone">Phone</label>
                        <input type="radio" id="contact-email" name="contact_method" value="email">
                        <label for="contact-email">Email</label>
                    `;
                }, 150);
            });
        </script>
        HTML);

    $page = visit('/');

    $page->click('Reveal radios')->radio('contact_method', 'email');

    expect($page->script('() => document.getElementById("contact-email").checked'))->toBeTrue()
        ->and($page->script('() => document.getElementById("contact-phone").checked'))->toBeFalse();
});

it('waits for a delayed select instead of falling back to visible text', function (): void {
    Route::get('/', fn (): string => <<<'HTML'
        <div>country</div>
        <button type="button" id="reveal-select">Reveal select</button>
        <div id="select-container"></div>

        <script>
            window.selectChanges = 0;

            document.getElementById('reveal-select').addEventListener('click', () => {
                setTimeout(() => {
                    document.getElementById('select-container').innerHTML = `
                        <label for="country-select">Country</label>
                        <select id="country-select" name="country">
                            <option value="us">United States</option>
                            <option value="ca">Canada</option>
                            <option value="mx">Mexico</option>
                        </select>
                    `;

                    document
                        .getElementById('country-select')
                        .addEventListener('change', () => window.selectChanges += 1);
                }, 150);
            });
        </script>
        HTML);

    $page = visit('/');

    $page->click('Reveal select')->select('country', 'Mexico');

    expect($page->script('() => document.getElementById("country-select").value'))->toBe('mx')
        ->and($page->script('() => window.selectChanges'))->toBe(1);
});

it('keeps assertion retries independent from action execution', function (): void {
    Route::get('/', fn (): string => <<<'HTML'
        <div id="status">Loading...</div>

        <script>
            setTimeout(() => {
                document.getElementById('status').textContent = 'Loaded later';
            }, 150);
        </script>
        HTML);

    visit('/')->assertSee('Loaded later');
});

it('supports existing field selector forms', function (string $selector): void {
    Route::get('/', fn (): string => <<<'HTML'
        <form>
            <label for="email-id">Email address</label>
            <input
                id="email-id"
                name="email_name"
                data-testid="email-field"
                data-test="email-field"
                placeholder="Enter email"
                type="text"
            >
        </form>
        HTML);

    $page = visit('/');

    $page->fill($selector, 'tony@example.com');

    expect($page->value('#email-id'))->toBe('tony@example.com');
})->with([
    'explicit css' => '#email-id',
    'data test' => '@email-field',
    'id' => 'email-id',
    'name' => 'email_name',
    'label' => 'Email address',
    'placeholder' => 'Enter email',
]);

it('supports existing checkable selector forms', function (string $selector, ?string $value = null): void {
    Route::get('/', fn (): string => <<<'HTML'
        <form>
            <input
                type="checkbox"
                id="terms-id"
                name="terms_name"
                data-testid="terms-field"
                data-test="terms-field"
            >
            <label for="terms-id">Accept terms</label>

            <input type="checkbox" id="contact-email" name="contact_method" value="email">
            <label for="contact-email">Email updates</label>
            <input type="checkbox" id="contact-sms" name="contact_method" value="sms">
            <label for="contact-sms">SMS updates</label>
        </form>
        HTML);

    $page = visit('/');

    $page->check($selector, $value);

    if ($value === null) {
        expect($page->script('() => document.getElementById("terms-id").checked'))->toBeTrue();

        return;
    }

    expect($page->script('() => document.getElementById("contact-email").checked'))->toBeTrue()
        ->and($page->script('() => document.getElementById("contact-sms").checked'))->toBeFalse();
})->with([
    'data test' => ['@terms-field', null],
    'id' => ['terms-id', null],
    'name' => ['terms_name', null],
    'label' => ['Accept terms', null],
    'name with value' => ['contact_method', 'email'],
]);

it('supports existing select selector forms', function (string $selector): void {
    Route::get('/', fn (): string => <<<'HTML'
        <form>
            <label for="country-id">Country</label>
            <select
                id="country-id"
                name="country_name"
                data-testid="country-field"
                data-test="country-field"
            >
                <option value="us">United States</option>
                <option value="ca">Canada</option>
                <option value="mx">Mexico</option>
            </select>
        </form>
        HTML);

    $page = visit('/');

    $page->select($selector, 'Canada');

    expect($page->value('#country-id'))->toBe('ca');
})->with([
    'explicit css' => '#country-id',
    'data test' => '@country-field',
    'id' => 'country-id',
    'name' => 'country_name',
    'label' => 'Country',
]);

it('fails clearly when no field control can be found', function (): void {
    Route::get('/', fn (): string => '<div>email</div>');

    visit('/')->fill('email', 'tony@example.com');
})->throws(ExpectationFailedException::class, 'No suitable field control was found for [email].');

it('fails clearly when no checkable control can be found', function (): void {
    Route::get('/', fn (): string => '<div>terms</div>');

    visit('/')->check('terms');
})->throws(ExpectationFailedException::class, 'No suitable checkable control was found for [terms].');

it('fails clearly when no selectable control can be found', function (): void {
    Route::get('/', fn (): string => '<div>country</div>');

    visit('/')->select('country', 'Canada');
})->throws(ExpectationFailedException::class, 'No suitable selectable control was found for [country].');
