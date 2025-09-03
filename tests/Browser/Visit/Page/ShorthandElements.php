<?php

declare(strict_types=1);

use Pest\Browser\Page;
use PHPUnit\Framework\ExpectationFailedException;

it('may asserts with global and local shorthand selector using object pages', function (): void {
    Route::get('/page-1', fn (): string => '
        <ul class="custom-breadcrumb">
            <li>Page 1</li>
        </ul>
        <input name="fullname" id="full-name" type="text">
        <input id="email" type="text">
        <textarea name="description" disabled></textarea>
    ');
    Route::get('/page-2', fn (): string => '
        <ul class="custom-breadcrumb">
            <li>Page 2</li>
        </ul>
    ');

    $page = visit(new ShorthandPage1);

    $page->assertSeeIn('@breadcrumb', 'Page 1')
        ->type('@name-input', 'nuno maduro')
        ->type('@email-input', 'nuno@pest.com')
        ->assertDisabled('@description-textarea');

    expect($page->value('#full-name'))->toBe('nuno maduro');
    expect($page->value('#email'))->toBe('nuno@pest.com');

    $page->navigate(new ShorthandPage2);
    $page->assertSeeIn('@breadcrumb', 'Page 2');
});

it('may fail when asserting wrong shorthand selector using object page', function (): void {
    Route::get('/page-1', fn (): string => '')->name('page');

    $page = visit(new ShorthandPage1);

    $page->assertSeeIn('@wrong-shorthand-selector', 'Pest');
})->throws(ExpectationFailedException::class);

abstract class ShorthandBasePage extends Page
{
    #[Override]
    final public static function siteElements(): array
    {
        return [
            '@breadcrumb' => '.custom-breadcrumb',
        ];
    }
}

final class ShorthandPage1 extends ShorthandBasePage
{
    public function url(): string
    {
        return '/page-1';
    }

    #[Override]
    public function elements(): array
    {
        return [
            '@name-input' => 'fullname',
            '@email-input' => '#email',
            '@description-textarea' => 'description',
        ];
    }
}

final class ShorthandPage2 extends ShorthandBasePage
{
    public function url(): string
    {
        return '/page-2';
    }
}
