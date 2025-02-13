<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Pest\Browser\Contracts\Operation;
use function sprintf;

final readonly class PageScreenshot implements Operation
{


    public function __construct(
        private string $path,
        private bool $fullPage = false,
    ) {
        //
    }

    /**
     * @inheritDoc
     */
    public function compile(): string
    {
        return sprintf(
            "await page.screenshot({ path: '%s', fullPage: %s });",
             $this->path,
            $this->fullPage ? 'true' : 'false'
        );
    }
}
