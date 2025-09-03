<?php

declare(strict_types=1);

use Pest\Browser\Api\Webpage;
use Pest\Browser\Page;

it('may chain object page methods', function (): void {
    Route::get('/', fn (): string => '
        <h3 id="header">Pest</h3>
        <input id="email" type="text">
        <textarea name="description" disabled></textarea>
    ');

    $page = visit(new ChainedPage);

    $page->enterEmail('nuno@pest.com')
        ->assertSeeIn('#header', 'Pest')
        ->descriptionDisabled();
});

final class ChainedPage extends Page
{
    public function url(): string
    {
        return '/';
    }

    public function enterEmail(Webpage $page, string $email): self
    {
        $page->type('email', $email);

        return $this;
    }

    public function descriptionDisabled(Webpage $page): self
    {
        $page->assertDisabled('description');

        return $this;
    }
}
