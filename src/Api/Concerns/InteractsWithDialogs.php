<?php

declare(strict_types=1);

namespace Pest\Browser\Api\Concerns;

use Closure;
use Pest\Browser\Playwright\Dialog;

trait InteractsWithDialogs
{
    /**
     * Set up a dialog handler for this page.
     */
    public function onDialog(Closure $handler): self
    {
        $this->page->onDialog($handler);

        return $this;
    }

    /**
     * Remove any previously set dialog handler.
     */
    public function removeDialogHandler(): self
    {
        $this->page->removeDialogHandler();

        return $this;
    }

    /**
     * Check if a dialog handler is currently set.
     */
    public function hasDialogHandler(): bool
    {
        return $this->page->hasDialogHandler();
    }

    /**
     * Set up automatic dialog acceptance for all future dialogs.
     */
    public function acceptAllDialogs(?string $promptText = null): self
    {
        $this->page->onDialog(function (Dialog $dialog) use ($promptText): void {
            $dialog->accept($promptText);
        });

        return $this;
    }

    /**
     * Set up automatic dialog dismissal for all future dialogs.
     */
    public function dismissAllDialogs(): self
    {
        $this->page->onDialog(function (Dialog $dialog): void {
            $dialog->dismiss();
        });

        return $this;
    }

    /**
     * Set up a dialog handler that accepts confirm dialogs and dismisses all others.
     */
    public function acceptingConfirms(): self
    {
        $this->page->onDialog(function (Dialog $dialog): void {
            if ($dialog->type() === 'confirm') {
                $dialog->accept();
            } else {
                $dialog->dismiss();
            }
        });

        return $this;
    }

    /**
     * Set up a dialog handler that dismisses confirm dialogs and accepts all others.
     */
    public function dismissingConfirms(): self
    {
        $this->page->onDialog(function (Dialog $dialog): void {
            if ($dialog->type() === 'confirm') {
                $dialog->dismiss();
            } else {
                $dialog->accept();
            }
        });

        return $this;
    }
}
