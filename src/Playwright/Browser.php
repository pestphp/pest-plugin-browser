<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

/**
 * @internal
 */
final class Browser
{
    /**
     * Browser context.
     */
    private BrowserContext $context;

    /**
     * Constructs browser.
     */
    public function __construct(
        public string $guid,
    ) {
        //
    }

    /**
     * Creates a new page in the current context.
     */
    public function newPage(): Page
    {
        return $this->newContext()->newPage();
    }

    /**
     * Creates a new browser context.
     */
    public function newContext(): BrowserContext
    {
        $response = Client::instance()->execute($this->guid, 'newContext');

        foreach ($response as $message) {
            if (isset($message['result']['context']['guid'])) {
                $this->context = new BrowserContext($message['result']['context']['guid']);
            }
        }

        return $this->context;
    }
}
