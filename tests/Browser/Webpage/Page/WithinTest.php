<?php

declare(strict_types=1);

use Pest\Browser\Page;

it('handles form interactions within scope from object page', function (): void {
    Route::get('/', fn (): string => '
        <div id="login-form">
            <input name="email" type="email">
            <input name="password" type="password">
            <select name="role">
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>
            <input type="checkbox" name="remember" value="1">
            <input type="radio" name="theme" value="light" id="light">
            <input type="radio" name="theme" value="dark" id="dark">
            <button type="submit">Login</button>
        </div>
    ');

    $page = visit(new ScopedPage);

    $page->within('@form', function ($page): void {
        $page->type('@email-field', 'user@example.com')
            ->type('@password-field', 'secret')
            ->select('@role-field', 'admin')
            ->check('@remember-field')
            ->radio('@theme-field', 'dark')
            ->press('Login');
    });

    expect($page->value('#login-form input[name="email"]'))->toBe('user@example.com');
    expect($page->value('#login-form input[name="password"]'))->toBe('secret');
    expect($page->value('#login-form select[name="role"]'))->toBe('admin');
});

it('works with nested scopes from object page', function (): void {
    Route::get('/', fn (): string => '
        <div id="outer">
            <div class="inner">
                <button id="inner-button" onclick="this.innerText = \'Inner button clicked\'">Inner Button</button>
                <p>Nested Text</p>
            </div>
        </div>
    ');

    $page = visit(new NestedScopesPage);

    $page->within('@outer-layer', function ($page): void {
        $page->within('@inner-layer', function ($innerBrowser): void {
            $innerBrowser->assertSee('Nested Text');
            $innerBrowser->click('@inner-btn')->assertSee('Inner button clicked');
        });
    });
});

final class NestedScopesPage extends Page
{
    public function url(): string
    {
        return '/';
    }

    public function elements(): array
    {
        return [
            '@outer-layer' => '#outer',
            '@inner-layer' => '.inner',
            '@inner-btn' => '#inner-button',
        ];
    }
}

final class ScopedPage extends Page
{
    public function url(): string
    {
        return '/';
    }

    public function elements(): array
    {
        return [
            '@form' => '#login-form',
            '@email-field' => 'email',
            '@password-field' => 'password',
            '@role-field' => 'role',
            '@remember-field' => 'remember',
            '@theme-field' => 'theme',
            '@submit-btn' => '#submit-button',
        ];
    }
}
