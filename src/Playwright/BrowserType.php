<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

/**
 * @internal
 */
final class BrowserType
{
    /**
     * Browser.
     */
    private Browser $browser;

    /**
     * Constructs browser type.
     */
    public function __construct(
        public string $guid,
        public string $name,
    ) {
        //
    }

    /**
     * Launches a browser of the specified type.
     */
    public function launch(): Browser
    {
        if (isset($this->browser)) {
            return $this->browser;
        }

        $response = Client::instance()->execute(
            $this->guid,
            'launch',
            ['browserType' => $this->name]
        );

        foreach ($response as $message) {
            if (isset($message['result']['browser']['guid'])) {
                $this->browser = new Browser($message['result']['browser']['guid']);
            }
        }

        return $this->browser;
    }
}
