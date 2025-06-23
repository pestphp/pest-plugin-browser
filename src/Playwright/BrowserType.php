<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

/**
 * @internal
 */
final class BrowserType
{
    /**
     * The browser instance.
     */
    private ?Browser $browser = null;

    /**
     * Creates a new browser type instance.
     */
    public function __construct(
        private readonly string $guid,
        private readonly string $name,
    ) {
        //
    }

    /**
     * Launches a browser of the specified type.
     */
    public function launch(): Browser
    {
        if ($this->browser instanceof Browser) {
            return $this->browser;
        }

        $response = Client::instance()->execute(
            $this->guid,
            'launch',
            ['browserType' => $this->name, 'headless' => true],
        );

        /** @var array{result: array{browser: array{guid: string|null}}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['browser']['guid'])) {
                $guid = $message['result']['browser']['guid'];

                $this->browser = new Browser($guid);
            }
        }

        assert($this->browser instanceof Browser, 'Browser instance was not created successfully.');

        return $this->browser;
    }

    /**
     * Closes the browser type.
     */
    public function close(): void
    {
        if ($this->browser instanceof Browser) {
            $this->browser->close();
        }

        $this->browser = null;
    }

    /**
     * Resets the browser type state, without closing the browser.
     */
    public function reset(): void
    {
        if ($this->browser instanceof Browser) {
            $this->browser->reset();
        }
    }
}
