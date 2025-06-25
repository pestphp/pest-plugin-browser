<?php

declare(strict_types=1);

use Pest\Browser\Api\AwaitableWebpage;
use Pest\Browser\Browsable;
use Pest\Plugin;

Plugin::uses(Browsable::class);

if (! function_exists('visit')) {
    /**
     * Browse to the given URL.
     *
     * @param  array<string, mixed>  $options
     */
    function visit(?string $url = null, array $options = []): AwaitableWebpage
    {
        return test()->visit($url, $options);
    }
}
