<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

/**
 * @internal
 */
final class BrowserFactory
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
        private readonly bool $headless,
        private readonly ?string $userAgent = null,
    ) {
        //
    }

    /**
     * Pre-launches the browser type with the given GUID.
     */
    public function prelaunch(string $guid): void
    {
        $this->browser = new Browser($guid);
    }

    /**
     * Launches a browser of the specified type.
     */
    public function launch(): Browser
    {
        if ($this->browser instanceof Browser) {
            return $this->browser;
        }

        $defaultOptions = [
            'browserType' => $this->name,
            'headless' => $this->headless,
            'ignoreHttpsErrors' => true,
            'bypassCSP' => true,
        ];

        if ($this->userAgent !== null) {
            $defaultOptions['userAgent'] = $this->userAgent;
        }

        $response = Client::instance()->execute(
            $this->guid,
            'launch',
            $defaultOptions,
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
