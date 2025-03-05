<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

/**
 * @internal
 */
final class Element
{
    /**
     * Constructs new element
     */
    public function __construct(
        public string $guid,
    ) {
        //
    }

    /**
     * Check if element is visible.
     */
    public function isVisible(): bool
    {
        $response = Client::instance()->execute($this->guid, 'isVisible');

        foreach ($response as $message) {
            if (isset($message['result']['value'])) {
                return (bool) $message['result']['value'];
            }
        }

        return false;
    }
}
