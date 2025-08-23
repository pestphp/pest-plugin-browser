<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('may hover an element', function (): void {
    Route::get('/', fn (): string => '
        <style>
            #after {
            display: none;
            }
            #switch-text:hover #before {
            display: none;
            }
            #switch-text:hover #after {
            display: inline;
            }
        </style>
        <div id="switch-text">
            <span id="before">before</span>
            <span id="after">after</span>
        </div>
    ');
    $page = visit('/');
    $page->assertUrlIs(url('/'));
    $page->assertSee('before');
    $page->assertDontSee('after');
    $page->hover('#switch-text');
    $page->assertSee('after');
    $page->assertDontSee('before');
});
