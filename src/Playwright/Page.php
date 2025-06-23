<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

use Generator;
use Pest\Browser\Playwright\Concerns\InteractsWithPlaywright;
use Pest\Browser\ServerManager;
use Pest\Browser\Support\ImageDiffSlider;
use Pest\Browser\Support\JavaScriptSerializer;
use Pest\Browser\Support\Screenshot;
use Pest\Browser\Support\Selector;
use Pest\TestSuite;
use PHPUnit\Framework\ExpectationFailedException;
use RuntimeException;

/**
 * @internal
 */
final class Page
{
    use InteractsWithPlaywright;

    /**
     * Whether the page has been closed.
     */
    private bool $closed = false;

    /**
     * Creates a new page instance.
     */
    public function __construct(
        private readonly Context $context,
        private readonly string $guid,
        private readonly string $frameGuid,
        private string $url = '',
    ) {
        //
    }

    /**
     * Get the browser context.
     */
    public function context(): Context
    {
        return $this->context;
    }

    /**
     * Get the current URL of the page.
     */
    public function url(): string
    {
        return $this->url;
    }

    /**
     * Navigates to the given URL.
     *
     * @param  array<string, mixed>  $options
     */
    public function goto(string $url, array $options = []): self
    {
        $url = ServerManager::instance()->http()->rewrite($url);

        $response = $this->sendMessage('goto', [
            ...['url' => $url, 'waitUntil' => 'load'],
            ...$options,
        ]);

        $this->processNavigationResponse($response);

        $this->waitForSelector('body', ['state' => 'attached']);

        return $this;
    }

    /**
     * Returns the meta title.
     */
    public function title(): string
    {
        $response = $this->sendMessage('title');

        return $this->processStringResponse($response);
    }

    /**
     * Get the value of an attribute of the first element matching the selector within the page.
     */
    public function getAttribute(string $selector, string $attribute): ?string
    {
        return $this->locator($selector)->getAttribute($attribute);
    }

    /**
     * Finds an element matching the specified selector.
     *
     * @deprecated Use locator($selector)->elementHandle() instead for Element compatibility, or use locator($selector) for Locator-first approach
     */
    public function querySelector(string $selector): ?Element
    {
        return $this->locator($selector)->elementHandle();
    }

    /**
     * Finds all elements matching the specified selector.
     *
     * @return Element[]
     */
    public function querySelectorAll(string $selector): array
    {
        $response = $this->sendMessage('querySelectorAll', ['selector' => $selector]);
        $elements = [];

        /** @var array{method?: string|null, params: array{type?: string|null, guid?: string}} $message */
        foreach ($response as $message) {
            if (
                isset($message['method'], $message['params']['type'], $message['params']['guid'])
                && $message['method'] === '__create__'
                && $message['params']['type'] === 'ElementHandle'
            ) {
                $elements[] = new Element($message['params']['guid']);
            }
        }

        return $elements;
    }

    /**
     * Create a locator for the specified selector.
     */
    public function locator(string $selector): Locator
    {
        return new Locator($this->frameGuid, $selector, true);
    }

    /**
     * Create a locator that matches elements by role.
     *
     * @param  array<string, string|bool>  $params
     */
    public function getByRole(string $role, array $params = []): Locator
    {
        return $this->locator(Selector::getByRoleSelector($role, $params));
    }

    /**
     * Create a locator that matches elements by test ID.
     */
    public function getByTestId(string $testId): Locator
    {
        $testIdAttributeName = 'data-testid';

        return $this->locator(Selector::getByTestIdSelector($testIdAttributeName, $testId));
    }

    /**
     * Create a locator that matches elements by alt text.
     */
    public function getByAltText(string $text, bool $exact = false): Locator
    {
        return $this->locator(Selector::getByAltTextSelector($text, $exact));
    }

    /**
     * Create a locator that matches elements by label text.
     */
    public function getByLabel(string $text, bool $exact = false): Locator
    {
        return $this->locator(Selector::getByLabelSelector($text, $exact));
    }

    /**
     * Create a locator that matches elements by placeholder text.
     */
    public function getByPlaceholder(string $text, bool $exact = false): Locator
    {
        return $this->locator(Selector::getByPlaceholderSelector($text, $exact));
    }

    /**
     * Create a locator that matches elements by text content.
     */
    public function getByText(string $text, bool $exact = false): Locator
    {
        return $this->locator(Selector::getByTextSelector($text, $exact));
    }

    /**
     * Create a locator that matches elements by title attribute.
     */
    public function getByTitle(string $text, bool $exact = false): Locator
    {
        return $this->locator(Selector::getByTitleSelector($text, $exact));
    }

    /**
     * Clicks the element matching the specified selector.
     */
    public function click(string $selector): self
    {
        $this->locatorNonStrict($selector)->click();

        return $this;
    }

    /**
     * Double-clicks the element matching the specified selector.
     */
    public function doubleClick(string $selector): self
    {
        $this->locatorNonStrict($selector)->dblclick();

        return $this;
    }

    /**
     * Gets the full HTML contents of the page, including the doctype.
     */
    public function content(): string
    {
        $response = $this->sendMessage('content');

        return $this->processStringResponse($response);
    }

    /**
     * Returns whether the element is enabled.
     */
    public function isEnabled(string $selector): bool
    {
        return $this->locatorNonStrict($selector)->isEnabled();
    }

    /**
     * Returns whether the element is visible.
     */
    public function isVisible(string $selector): bool
    {
        return $this->locatorNonStrict($selector)->isVisible();
    }

    /**
     * Returns whether the element is hidden.
     */
    public function isHidden(string $selector): bool
    {
        return ! $this->isVisible($selector);
    }

    /**
     * Returns whether the element is editable.
     */
    public function isEditable(string $selector): bool
    {
        return $this->locatorNonStrict($selector)->isEditable();
    }

    /**
     * Returns whether the element is disabled.
     */
    public function isDisabled(string $selector): bool
    {
        return $this->locatorNonStrict($selector)->isDisabled();
    }

    /**
     * Fills a form field with the given value.
     */
    public function fill(string $selector, string $value): self
    {
        $this->locatorNonStrict($selector)->fill($value);

        return $this;
    }

    /**
     * Returns element's inner text.
     */
    public function innerText(string $selector): string
    {
        return $this->locatorNonStrict($selector)->innerText();
    }

    /**
     * Returns element's text content.
     */
    public function textContent(string $selector = 'html'): ?string
    {
        return $this->locatorNonStrict($selector)->textContent();
    }

    /**
     * Returns the input value for input elements.
     */
    public function inputValue(string $selector): string
    {
        return $this->locatorNonStrict($selector)->inputValue();
    }

    /**
     * Checks whether the element is checked (for checkboxes and radio buttons).
     */
    public function isChecked(string $selector): bool
    {
        return $this->locatorNonStrict($selector)->isChecked();
    }

    /**
     * Checks the element (for checkboxes and radio buttons).
     */
    public function check(string $selector): self
    {
        $this->locatorNonStrict($selector)->check();

        return $this;
    }

    /**
     * Unchecks the element (for checkboxes and radio buttons).
     */
    public function uncheck(string $selector): self
    {
        $this->locatorNonStrict($selector)->uncheck();

        return $this;
    }

    /**
     * Hovers over the element matching the specified selector.
     *
     * @param  array<int, string>|null  $modifiers
     * @param  array<int, int>|null  $position
     */
    public function hover(
        string $selector,
        ?bool $force = null,
        ?array $modifiers = null,
        ?bool $noWaitAfter = null,
        ?array $position = null,
        ?bool $strict = null,
        ?int $timeout = null,
        ?bool $trial = null
    ): self {
        $options = [];

        if ($force !== null) {
            $options['force'] = $force;
        }
        if ($modifiers !== null) {
            $options['modifiers'] = $modifiers;
        }
        if ($noWaitAfter !== null) {
            $options['noWaitAfter'] = $noWaitAfter;
        }
        if ($position !== null) {
            $options['position'] = $position;
        }
        if ($strict !== null) {
            $options['strict'] = $strict;
        }
        if ($timeout !== null) {
            $options['timeout'] = $timeout;
        }
        if ($trial !== null) {
            $options['trial'] = $trial;
        }

        $this->locatorNonStrict($selector)->hover($options);

        return $this;
    }

    /**
     * Focuses the element matching the specified selector.
     */
    public function focus(string $selector): self
    {
        $this->locatorNonStrict($selector)->focus();

        return $this;
    }

    /**
     * Presses a key on the element matching the specified selector.
     */
    public function press(string $selector, string $key): self
    {
        $this->locatorNonStrict($selector)->press($key);

        return $this;
    }

    /**
     * Types text into the element matching the specified selector.
     */
    public function type(string $selector, string $text): self
    {
        $this->locatorNonStrict($selector)->type($text);

        return $this;
    }

    /**
     * Waits for the specified load state.
     */
    public function waitForLoadState(string $state = 'load'): self
    {
        Client::instance()->execute(
            $this->guid,
            'waitForLoadState',
            ['state' => $state]
        );

        return $this;
    }

    /**
     * Waits for navigation to the specified URL.
     */
    public function waitForURL(string $url): self
    {
        Client::instance()->execute(
            $this->guid,
            'waitForURL',
            ['url' => $url]
        );

        return $this;
    }

    /**
     * Waits for the selector to satisfy state option.
     *
     * @param  array<string, mixed>|null  $options  Additional options like state, strict, timeout
     */
    public function waitForSelector(string $selector, ?array $options = null): ?Element
    {
        $locator = $this->locatorNonStrict($selector);
        $locator->waitFor($options);

        return $locator->elementHandle();
    }

    /**
     * Performs drag and drop operation.
     */
    public function dragAndDrop(string $source, string $target): self
    {
        $sourceLocator = $this->locatorNonStrict($source);
        $targetLocator = $this->locatorNonStrict($target);

        $sourceLocator->dragTo($targetLocator);

        return $this;
    }

    /**
     * Sets the content of the page.
     */
    public function setContent(string $html): self
    {
        $response = $this->sendMessage('setContent', ['html' => $html]);
        $this->processVoidResponse($response);

        return $this;
    }

    /**
     * Selects option(s) in a select element.
     *
     * @param  array<int, string>|string|null  $value
     * @param  array<int, string>|string|null  $label
     * @param  array<int, int>|int|null  $index
     */
    public function selectOption(
        string $selector,
        array|string|null $value = null,
        array|string|null $label = null,
        array|int|null $index = null,
        ?bool $force = null,
        ?bool $noWaitAfter = null,
        ?bool $strict = null,
        ?int $timeout = null
    ): self {
        $options = [];

        // Add the appropriate selection criteria - choose only one
        if ($label !== null) {
            $options['label'] = is_array($label) ? $label : [$label];
        } elseif ($index !== null) {
            $options['index'] = is_array($index) ? $index : [$index];
        }

        // Add optional parameters
        if ($force !== null) {
            $options['force'] = $force;
        }
        if ($noWaitAfter !== null) {
            $options['noWaitAfter'] = $noWaitAfter;
        }
        if ($strict !== null) {
            $options['strict'] = $strict;
        }
        if ($timeout !== null) {
            $options['timeout'] = $timeout;
        }

        $this->locatorNonStrict($selector)->selectOption($value, $options);

        return $this;
    }

    /**
     * Evaluates a JavaScript expression in the page context.
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
     * Evaluates a JavaScript expression and returns a JSHandle.
     */
    public function evaluateHandle(string $pageFunction, mixed $arg = null): JSHandle
    {
        $params = [
            'expression' => $pageFunction,
            'arg' => JavaScriptSerializer::serializeArgument($arg),
        ];

        $response = $this->sendMessage('evaluateExpressionHandle', $params);

        foreach ($response as $message) {
            if (
                is_array($message) && is_array($message['params'] ?? null)
                && isset($message['method'], $message['params']['type'], $message['params']['guid'])
                && $message['method'] === '__create__'
                && $message['params']['type'] === 'JSHandle'
            ) {
                return new JSHandle((string) $message['params']['guid']); // @phpstan-ignore-line
            }

            if (
                is_array($message)
                && is_array($message['result'] ?? null)
                && isset($message['result']['handle'])
            ) {
                return new JSHandle($message['result']['handle']['guid']); // @phpstan-ignore-line
            }
        }

        throw new RuntimeException('Failed to create JSHandle from evaluate response');
    }

    /**
     * Navigates to the next page in the history.
     */
    public function forward(): self
    {
        $response = $this->sendMessage('goForward');
        $this->processNavigationResponse($response);

        return $this;
    }

    /**
     * Navigates to the previous page in the history.
     */
    public function back(): self
    {
        $response = $this->sendMessage('goBack');
        $this->processNavigationResponse($response);

        return $this;
    }

    /**
     * Reloads the current page.
     */
    public function reload(): self
    {
        $response = $this->sendMessage('reload', ['waitUntil' => 'load']);
        $this->processNavigationResponse($response);

        return $this;
    }

    /**
     * Make screenshot of the page.
     */
    public function screenshot(?string $filename = null): void
    {
        $binary = $this->screenshotBinary();

        if ($binary === null) {
            return;
        }

        Screenshot::save($binary, $filename);
    }

    /**
     * Make a screenshot of the page and compare it with the expected one.
     *
     * If the screenshot does not match, it will throw an ExpectationFailedException.
     * The diff will be saved in the screenshots directory.
     *
     * @throws ExpectationFailedException
     */
    public function toMatchScreenshot(bool $showDiff = false): void
    {
        $actualImageBlob = $this->screenshotBinary();

        try {
            expect($actualImageBlob)->toMatchSnapshot();
        } catch (ExpectationFailedException) {
            [$snapshotName, $expectedImageBlob] = TestSuite::getInstance()->snapshots->get();

            $response = Client::instance()->execute(
                $this->guid,
                'expectScreenshot',
                [
                    'type' => 'png', 'fullPage' => true, 'hideCaret' => true,
                    'isNot' => false, 'expected' => $expectedImageBlob,
                ]
            );

            // keep only the filename without the path and extension
            $snapshotName = pathinfo($snapshotName, PATHINFO_FILENAME);
            /** @var array{result: array{diff: string|null}} $message */
            foreach ($response as $message) {
                if (isset($message['result']['diff'])) {
                    $sliderDir = Screenshot::dir().'/.sliders';

                    if (is_dir($sliderDir) === false) {
                        mkdir($sliderDir, 0755, true);
                    }

                    $sliderPath = $sliderDir.'/'.$snapshotName.'.html';
                    $diffImage = $showDiff ? $message['result']['diff'] : $actualImageBlob;

                    // @phpstan-ignore-next-line
                    file_put_contents($sliderPath, ImageDiffSlider::generate(base64_decode($expectedImageBlob), base64_decode((string) $diffImage), test()->name()));

                    throw new ExpectationFailedException('snapshot does not match the current screenshot. Check '.$sliderPath);
                }
            }

            throw new ExpectationFailedException('No "visual" differences found, but the snapshot does not match the current screenshot.');
        }
    }

    /**
     * Closes the page.
     */
    public function close(): void
    {
        if ($this->context->browser()->isClosed()
            || $this->context->isClosed()
            || $this->closed) {
            return;
        }

        $response = $this->sendMessage('close');

        $this->processVoidResponse($response);

        $this->closed = true;
    }

    /**
     * Checks if the page is closed.
     */
    public function isClosed(): bool
    {
        return $this->closed;
    }

    /**
     * Create a non-strict locator for internal use.
     */
    private function locatorNonStrict(string $selector): Locator
    {
        return new Locator($this->frameGuid, $selector, false);
    }

    /**
     * Screenshots the page and returns the binary data.
     */
    private function screenshotBinary(): ?string
    {
        $response = Client::instance()->execute(
            $this->guid,
            'screenshot',
            ['type' => 'png', 'fullPage' => true, 'hideCaret' => true]
        );

        /** @var array{result: array{binary: string|null}} $message */
        foreach ($response as $message) {
            if (isset($message['result']['binary'])) {
                return $message['result']['binary'];
            }
        }

        return null;
    }

    /**
     * Override processNavigationResponse for Page specific behavior
     */
    private function processNavigationResponse(Generator $response): void
    {
        /** @var array{method: string|null, params: array{url: string|null}} $message */
        foreach ($response as $message) {
            if (isset($message['method']) && $message['method'] === 'navigated') {
                $this->url = $message['params']['url'] ?? '';
            }
        }
    }

    /**
     * Send a message to the frame (for frame-related operations)
     *
     * @param  array<string, mixed>  $params
     */
    private function sendMessage(string $method, array $params = []): Generator
    {
        // Use frame GUID for frame-related operations, page GUID for page-level operations
        $targetGuid = $this->isPageLevelOperation($method) ? $this->guid : $this->frameGuid;

        return Client::instance()->execute($targetGuid, $method, $params);
    }

    /**
     * Determine if an operation should use the page GUID vs frame GUID
     */
    private function isPageLevelOperation(string $method): bool
    {
        $pageLevelOperations = [
            'close',
            'Network.setExtraHTTPHeaders',
            'goForward',
            'goBack',
            'reload',
            'screenshot',
            'waitForLoadState',
            'waitForURL',
        ];

        return in_array($method, $pageLevelOperations, true);
    }
}
