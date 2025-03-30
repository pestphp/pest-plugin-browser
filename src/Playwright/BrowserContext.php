<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

/**
 * @internal
 */
final class BrowserContext
{
    /**
     * Frame.
     */
    public Frame $frame;

    /**
     * Page.
     */
    public Page $page;

    /**
     * Constructs browser context.
     */
    public function __construct(
        public string $guid
    ) {
        //
    }

    /**
     * Creates a new page in the context.
     */
    public function newPage(): Page
    {
        $response = Client::instance()->execute($this->guid, 'newPage');

        /** @var array{method: string|null, params: array{type: string|null, guid: string, initializer: array{url: string}}, result: array{page: array{guid: string|null}}} $message */
        foreach ($response as $message) {
            if (isset($message['method']) && $message['method'] === '__create__' && (isset($message['params']['type']) && $message['params']['type'] === 'Frame')) {
                $this->frame = new Frame($message['params']['guid'], $message['params']['initializer']['url']);
            }

            if (isset($message['result']['page']['guid'])) {
                $this->page = new Page($message['result']['page']['guid'], $this->frame);
            }
        }

        return $this->page;
    }
}
