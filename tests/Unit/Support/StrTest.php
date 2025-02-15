<?php

declare(strict_types=1);

use Pest\Browser\Support\Str;

test('one character non-regex string', function () {
    $string = 'a';

    $result = Str::isRegex($string);

    expect($result)->toBeFalse();
});

it('detects regex expressions', function () {
    $regex = '/^.*$/';

    $result = Str::isRegex($regex);

    expect($result)->toBeTrue();
});

it('detects non-regex expressions', function () {
    $string = 'string';

    $result = Str::isRegex($string);

    expect($result)->toBeFalse();
});
