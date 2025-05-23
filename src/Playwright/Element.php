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

        /** @var array{result: array{value: bool|null}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['value'])) {
                return $message['result']['value'];
            }
        }

        return false;
    }

    /**
     * Check if element is checked.
     */
    public function isChecked(): bool
    {
        $response = Client::instance()->execute($this->guid, 'isChecked');

        /** @var array{result: array{value: bool|null}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['value'])) {
                return $message['result']['value'];
            }
        }

        return false;
    }

    /**
     * Check element.
     */
    public function check(): void
    {
        $response = Client::instance()->execute($this->guid, 'check');

        foreach ($response as $message) {
            // read all messages to clear the response
        }
    }

    /**
     * Uncheck element.
     */
    public function uncheck(): void
    {
        $response = Client::instance()->execute($this->guid, 'uncheck');

        foreach ($response as $message) {
            // read all messages to clear the response
        }
    }

    /**
     * Get the Attribute of the element.
     */
    public function getAttribute(string $attribute): ?string
    {
        $response = Client::instance()->execute($this->guid, 'getAttribute', ['name' => $attribute]);

        /** @var array{result: array{value: string|null}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['value'])) {
                return $message['result']['value'];
            }
        }

        return null;
    }
}
