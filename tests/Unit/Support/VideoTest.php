<?php

declare(strict_types=1);

use Pest\Browser\Support\Video;

it('returns the videos directory path', function (): void {
    expect(Video::dir())->toEndWith('/tests/Browser/Videos');
});

it('videos directory is under tests/Browser', function (): void {
    expect(Video::dir())->toContain('tests/Browser/Videos');
});
