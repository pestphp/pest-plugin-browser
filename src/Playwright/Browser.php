<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

use Pest\Browser\Enums\TracingOption;
use Pest\Browser\Exceptions\BrowserAlreadyClosedException;

/**
 * @internal
 */
final class Browser
{
    use Concerns\InteractsWithPlaywright;

    /**
     * Indicates whether the browser is closed.
     */
    private bool $closed = false;

    /**
     * The browser's contexts.
     *
     * @var array<int, Context>
     */
    private array $contexts = [];

    /**
     * Creates a new browser instance.
     */
    public function __construct(
        private readonly string $guid,
    ) {
        //
    }

    /**
     * Creates a new browser context.
     *
     * @param  array<string, mixed>  $options  Options for the context, e.g. ['hasTouch' => true]
     */
    public function newContext(array $options = []): Context
    {
        if ($this->closed) {
            throw new BrowserAlreadyClosedException('The browser is already closed.');
        }

        $response = Client::instance()->execute($this->guid, 'newContext', $options);

        /** @var array{result: array{context: array{guid: string|null}}, params: array{type: string|null, guid: string}} $message */
        foreach ($response as $message) {
            if (isset($message['params']['type']) && $message['params']['type'] === 'Tracing') {
                $tracing = new Tracing($message['params']['guid']);
            }
            if (isset($message['result']['context']['guid'])) {
                assert(isset($tracing), 'Tracing object was not initialized.');
                $context = new Context($this, $tracing, $message['result']['context']['guid']);
            }
        }

        assert(isset($tracing), 'Tracing object was not initialized.');
        assert(isset($context), 'Browser context was not created successfully.');

        // Auto start tracing
        if (Playwright::tracingOption() !== TracingOption::OFF) {
            $tracing->start([
                'screenshots' => true,
                'snapshots' => true,
            ]);
            $tracing->startChunk();
        }

        $this->contexts[] = $context;

        return $context;
    }

    /**
     * Closes the browser.
     */
    public function close(): void
    {
        if ($this->closed) {
            return;
        }

        $response = $this->sendMessage('close');
        $this->processVoidResponse($response);

        $this->contexts = [];
        $this->closed = true;
    }

    /**
     * Checks if the browser is closed.
     */
    public function isClosed(): bool
    {
        return $this->closed;
    }

    /**
     * Resets the browser state.
     */
    public function reset(): void
    {
        if ($this->closed) {
            return;
        }

        foreach ($this->contexts as $context) {
            $context->close();
        }

        $this->contexts = [];
    }
}
