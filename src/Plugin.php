<?php

declare(strict_types=1);

namespace Pest\Browser;

use Pest\Contracts\Plugins\Bootable;
use Pest\Contracts\Plugins\Terminable;
use Pest\Plugins\Parallel;

/**
 * @internal
 */
final readonly class Plugin implements Bootable, Terminable // @pest-arch-ignore-line
{
    /**
     * Boots the plugin.
     */
    public function boot(): void
    {
        if (Parallel::isWorker() === false) {
            ServerManager::instance()->playwright()->start();
        }
    }

    /**
     * Terminates the plugin.
     */
    public function terminate(): void
    {
        if (Parallel::isWorker() === false) {
            ServerManager::instance()->playwright()->stop();
        }

        ServerManager::instance()->http()->stop();
    }
}
