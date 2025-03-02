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
        $response = Client::getInstance()->execute($this->guid, 'newPage');

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
