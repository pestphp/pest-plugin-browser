<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('can click element within a scoped selector', function (): void {
    Route::get('/', fn (): string => '
        <div id="sidebar">
            <button id="sidebar-button">Sidebar Button</button>
            <a href="/sidebar">Sidebar Link</a>
        </div>
        <div id="content">
            <button id="content-button">Content Button</button>
            <a href="/content">Content Link</a>
        </div>
    ');

    Route::get('/sidebar', fn (): string => 'Sidebar Page');
    Route::get('/content', fn (): string => 'Content Page');

    $page = visit('/');

    $page->within('#sidebar', function ($page): void {
        $page->click('Sidebar Button');
    })
        ->within('#sidebar', function ($page): void {
            $page->click('Sidebar Link');
        })
        ->within('#content', function ($page): void {
            $page->assertDontSee('Sidebar Link');
        })
        ->assertUrlIs(url('/sidebar'))
        ->assertSee('Sidebar Page');
});

it('can type in input within a scoped selector', function (): void {
    Route::get('/', fn (): string => '
        <div id="form1">
            <input name="username" placeholder="Username">
            <button type="submit">Submit Form 1</button>
        </div>
        <div id="form2">
            <input name="username" placeholder="Email">
            <button type="submit">Submit Form 2</button>
        </div>
    ');

    $page = visit('/');

    $page->within('#form1', function ($page): void {
        $page->type('username', 'john_doe');
    });

    $page->within('#form2', function ($page): void {
        $page->type('username', 'john@example.com');
    });

    expect($page->value('#form1 input[name="username"]'))->toBe('john_doe');
    expect($page->value('#form2 input[name="username"]'))->toBe('john@example.com');
});

it('can assert text within a scoped selector', function (): void {
    Route::get('/', fn (): string => '
        <div id="header">
            <h1>Welcome</h1>
            <p>Header content</p>
        </div>
        <div id="footer">
            <h1>Contact</h1>
            <p>Footer content</p>
        </div>
    ');

    $page = visit('/');

    $page->within('#header', function ($page): void {
        $page->assertSee('Welcome');
        $page->assertSee('Header content');
        $page->assertDontSee('Footer content');
    });

    $page->within('#footer', function ($page): void {
        $page->assertSee('Contact');
        $page->assertSee('Footer content');
        $page->assertDontSee('Header content');
    });
});

it('can use css selectors within scope', function (): void {
    Route::get('/', fn (): string => '
        <div class="container">
            <div class="item">
                <button class="btn" onclick="this.innerText = \'Clicked\'">First Button</button>
            </div>
            <div class="item">
                <button class="btn">Second Button</button>
            </div>
        </div>
        <div class="other">
            <button class="btn">Other Button</button>
        </div>
    ');

    $page = visit('/');

    $page->within('.container', function ($page): void {
        $page->click('.item:first-child .btn');
    });

    $page->within('.container .item:first-child', function ($page): void {
        $page->assertSee('Clicked');
    });
});

it('works with data-test selectors within scope', function (): void {
    Route::get('/', fn (): string => '
        <div data-testid="sidebar">
            <button data-testid="action-btn" onclick="this.innerText = \'Sidebar Clicked\'">Sidebar Action</button>
        </div>
        <div data-testid="content">
            <button data-testid="action-btn" onclick="this.innerText = \'Content Clicked\'">Content Action</button>
        </div>
    ');

    $page = visit('/');

    $page->within('[data-testid="sidebar"]', function ($page): void {
        $page->click('@action-btn')->assertSee('Sidebar Clicked');
    });
    $page->within('[data-testid="content"]', function ($page): void {
        $page->assertSee('Content Action')->assertDontSee('Content Clicked');
        $page->click('@action-btn')->assertSee('Content Clicked');
    });
});

it('works with nested scopes', function (): void {
    Route::get('/', fn (): string => '
        <div id="outer">
            <div class="inner">
                <button id="inner-button" onclick="this.innerText = \'Inner button clicked\'">Inner Button</button>
                <p>Nested Text</p>
            </div>
        </div>
    ');

    $page = visit('/');

    $page->within('#outer', function ($page): void {
        $page->within('.inner', function ($innerBrowser): void {
            $innerBrowser->assertSee('Nested Text');
            $innerBrowser->click('#inner-button')->assertSee('Inner button clicked');
        });
    });
});

it('handles form interactions within scope', function (): void {
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

    $page = visit('/');

    $page->within('#login-form', function ($page): void {
        $page->type('email', 'user@example.com')
            ->type('password', 'secret')
            ->select('role', 'admin')
            ->check('remember')
            ->radio('theme', 'dark')
            ->press('Login');
    });

    expect($page->value('#login-form input[name="email"]'))->toBe('user@example.com');
    expect($page->value('#login-form input[name="password"]'))->toBe('secret');
    expect($page->value('#login-form select[name="role"]'))->toBe('admin');
});
