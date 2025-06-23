<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

use Pest\Browser\Playwright\Concerns\InteractsWithPlaywright;
use Pest\Browser\Support\JavaScriptSerializer;

/**
 * JSHandle represents a handle to a JavaScript object in the browser.
 * It can be used to interact with the object or evaluate expressions on it.
 *
 * @internal
 */
final readonly class JSHandle
{
    use InteractsWithPlaywright;

    /**
     * Creates a new js handle instance.
     */
    public function __construct(
        private string $guid,
    ) {
        //
    }

    /**
     * Get the unique identifier for this handle.
     */
    public function guid(): string
    {
        return $this->guid;
    }

    /**
     * Evaluate a JavaScript expression on this handle.
     */
    public function evaluate(string $pageFunction, mixed $arg = null): mixed
    {
        $params = [
            'expression' => $pageFunction,
            'arg' => JavaScriptSerializer::serializeArgument($arg),
        ];

        $response = $this->sendMessage('evaluateExpression', $params);

        return $this->processResultResponse($response);
    }

    /**
     * Get the JSON representation of this handle's value.
     */
    public function jsonValue(): mixed
    {
        $response = $this->sendMessage('jsonValue');

        return $this->processResultResponse($response);
    }

    /**
     * Dispose of this handle.
     */
    public function dispose(): void
    {
        $response = $this->sendMessage('dispose');
        $this->processVoidResponse($response);
    }

    /**
     * Get the string representation of this handle.
     */
    public function toString(): string
    {
        $value = $this->jsonValue();

        if (is_string($value)) {
            return $value;
        }

        return (string) $value; // @phpstan-ignore-line // temporary
    }
}
