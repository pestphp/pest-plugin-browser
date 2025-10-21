<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright\Concerns;

use Generator;
use Pest\Browser\Playwright\Client;
use Pest\Browser\Playwright\Element;
use Pest\Browser\Support\JavaScriptSerializer;

/**
 * @internal
 */
trait InteractsWithPlaywright
{
    /**
     * Start holding down key
     */
    public function keyDown(string $key): void
    {
        $response = $this->sendMessage('keyboardDown', ['key' => $key]);
        $this->processVoidResponse($response);
    }

    /**
     * Let go of key
     */
    public function keyUp(string $key): void
    {
        $response = $this->sendMessage('keyboardUp', ['key' => $key]);
        $this->processVoidResponse($response);
    }

    /**
     * Send a message to the server via the channel
     *
     * @param  array<string, mixed>  $params
     */
    private function sendMessage(string $method, array $params = []): Generator
    {
        return Client::instance()->execute($this->guid, $method, $params);
    }

    /**
     * Process response and extract result value
     */
    private function processResultResponse(Generator $response): mixed
    {
        /** @var array{result?: array{value: mixed}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['value'])) {
                return JavaScriptSerializer::parseValue($message['result']['value']);
            }
        }

        return null;
    }

    /**
     * Process response and extract string result
     */
    private function processStringResponse(Generator $response): string
    {
        $result = $this->processResultResponse($response);

        if (! is_string($result) && ! is_numeric($result)) {
            return '';
        }

        return (string) $result;
    }

    /**
     * Process response and extract nullable string result
     */
    private function processNullableStringResponse(Generator $response): ?string
    {
        $result = $this->processResultResponse($response);

        if ($result === null) {
            return null;
        }

        if (! is_string($result) && ! is_numeric($result)) {
            return null;
        }

        return (string) $result;
    }

    /**
     * Process response and extract boolean result
     */
    private function processBooleanResponse(Generator $response): bool
    {
        $result = $this->processResultResponse($response);

        if (! is_bool($result)) {
            return false;
        }

        return $result;
    }

    /**
     * Process response and return array value.
     *
     * @return array<mixed>
     */
    private function processArrayResponse(Generator $response): array
    {
        $result = $this->processResultResponse($response);

        return (array) ($result ?? []);
    }

    /**
     * Process response consuming all messages
     */
    private function processVoidResponse(Generator $response): void
    {
        iterator_to_array($response);
    }

    /**
     * Process response to handle element creation messages.
     */
    private function processElementCreationResponse(Generator $response): ?Element
    {
        /** @var array{method: string|null, params: array{type: string|null, guid: string}} $message */
        foreach ($response as $message) {
            if (
                // @phpstan-ignore-next-line
                isset($message['method'], $message['params']['type'], $message['params']['guid'])
                && $message['method'] === '__create__'
                && $message['params']['type'] === 'ElementHandle'
            ) {
                return new Element($message['params']['guid']);
            }
        }

        return null;
    }

    /**
     * Process response to return Element instance from result value.
     * Used by Element and Locator classes.
     */
    private function processElementResponse(Generator $response): ?Element
    {
        $result = $this->processResultResponse($response);

        if (! is_string($result)) {
            return null;
        }

        return new Element($result);
    }

    /**
     * Process response to handle multiple element creation messages.
     * Used by the Element class.
     *
     * @return array<Element>
     */
    private function processMultipleElementCreationResponse(Generator $response): array
    {
        $elements = [];

        /** @var array{method?: string|null, params: array{type?: string|null, guid?: string}} $message */
        foreach ($response as $message) {
            if (
                isset($message['method'], $message['params']['type'], $message['params']['guid'])
                && $message['method'] === '__create__'
                && $message['params']['type'] === 'ElementHandle'
            ) {
                $elements[] = new Element($message['params']['guid']);
            }
        }

        return $elements;
    }

    /**
     * Process response and extract binary result
     */
    private function processBinaryResponse(Generator $response): string
    {
        /** @var array{result: array{binary: string|null}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['binary'])) {
                return $message['result']['binary'];
            }
        }

        return '';
    }

    /**
     * Process response to handle Frame result messages.
     * Returns frame GUID if a Frame object was returned.
     */
    private function processFrameCreationResponse(Generator $response): ?string
    {
        /** @var array{result?: array{frame?: array{guid?: string}}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['frame']['guid'])) {
                return $message['result']['frame']['guid'];
            }
        }

        return null;
    }
}
