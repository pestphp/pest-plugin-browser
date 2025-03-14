<?php

declare(strict_types=1);

describe('assert script', function () {
    it('passes', function ($expression, $expected) {
        $this->visit('/')
            ->assertScript($expression, $expected);
    })->with([
        ['document.querySelector("title").textContent.includes("Pest")', true],
        ['document.querySelector("title").textContent.includes("Hamburguer and Pizza")', false],
        ['document.querySelector("title").textContent', 'Pest Plugin Browser - Playground'],
        ['document.querySelectorAll("title").length', 1],
        ['parseFloat("1.25")', 1.25],
        ['JSON.parse("[1,2]")', [1, 2]],
        ['document.querySelector(".non-existent-element")', null],
    ]);
});
