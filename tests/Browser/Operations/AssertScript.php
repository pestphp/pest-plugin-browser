<?php

declare(strict_types=1);

describe('assert script', function () {
    it('passes', function ($expression, $expected) {
        $this->visit('https://laravel.com')
            ->assertScript($expression, $expected);
    })->with([
        ['document.querySelector("title").textContent.includes("Laravel")', true],
        ['document.querySelector("title").textContent.includes("Symfony")', false],
        ['document.querySelector("title").textContent', 'Laravel - The PHP Framework For Web Artisans'],
        ['document.querySelectorAll("title").length', 1],
        ['parseFloat("1.25")', 1.25],
        ['JSON.parse("[1,2]")', [1, 2]],
        ['document.querySelector(".non-existent-element")', null],
    ]);
});
