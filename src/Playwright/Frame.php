<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

/**
 * @internal
 */
final class Frame
{
    /**
     * Constructs new frame.
     */
    public function __construct(
        public string $guid,
        public string $url,
    ) {
        //
    }

    /**
     * Get the value of an attribute of the first element matching the selector within the frame.
     */
    public function getAttribute(string $selector, string $attribute): ?string
    {
        $response = Client::instance()->execute(
            $this->guid,
            'getAttribute',
            ['selector' => $selector, 'name' => $attribute],
        );

        foreach ($response as $message) {
            if (isset($message['result']['value'])) {
                return (string) $message['result']['value'];
            }
        }

        return null;
    }
}
