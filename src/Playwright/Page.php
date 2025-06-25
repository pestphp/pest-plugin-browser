<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

use Generator;
use Pest\Browser\Execution;
use Pest\Browser\ServerManager;
use Pest\Browser\Support\ImageDiffSlider;
use Pest\Browser\Support\JavaScriptSerializer;
use Pest\Browser\Support\Screenshot;
use Pest\Browser\Support\Selector;
use Pest\TestSuite;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;
use ReflectionClass;
use RuntimeException;

/**
 * @internal
 */
final class Page
{
    use Concerns\InteractsWithPlaywright;

    /**
     * Whether the page has been closed.
     */
    private bool $closed = false;

    /**
     * Enable or disable strict locators.
     */
    private bool $strictLocators = true;

    /**
     * Creates a new page instance.
     */
    public function __construct(
        private readonly Context $context,
        private readonly string $guid,
        private readonly string $frameGuid,
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
        $url = $this->await(
            fn (): mixed => $this->evaluate('() => window.location.href'),
        );

        assert(is_string($url), 'Expected URL to be a string, got: '.gettype($url));

        return $url;
    }

    /**
     * Performs the given callback in unstrict mode.
     *
     * @template TReturn
     *
     * @param  callable(Page): TReturn  $callback
     * @return TReturn
     */
    public function unstrict(callable $callback): mixed
    {
        try {
            $this->strictLocators = false;

            return $callback($this);
        } finally {
            $this->strictLocators = true;
        }
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

        $this->processVoidResponse($response);

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
        return new Locator($this->frameGuid, $selector, $this->strictLocators);
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
     * Create a locator that matches elements by given ID attribute.
     */
    public function getById(string $id): Locator
    {
        return $this->locator(Selector::getByIdSelector($id));
    }

    /**
     * Create a locator that matches elements by given name attribute.
     */
    public function getByName(string $name): Locator
    {
        return $this->locator(Selector::getByNameSelector($name));
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
     * Gets the text content of the body element.
     */
    public function textContent(): ?string
    {
        return $this->locator('body')->textContent();
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
        $locator = $this->locator($selector);
        $locator->waitFor($options);

        return $locator->elementHandle();
    }

    /**
     * Awaits for a condition to be met, retrying until the timeout is reached.
     */
    public function await(callable $callback, int|float $timeout = 1): mixed
    {
        $originalCount = Assert::getCount();

        $start = microtime(true);
        $end = $start + $timeout;

        while (microtime(true) < $end) {
            try {
                return $callback();
            } catch (ExpectationFailedException) {
                //
            }

            $this->resetAssertions($originalCount);

            Execution::instance()->pause(0.01);
        }

        return $callback();
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
        $this->processVoidResponse($response);

        return $this;
    }

    /**
     * Navigates to the previous page in the history.
     */
    public function back(): self
    {
        $response = $this->sendMessage('goBack');
        $this->processVoidResponse($response);

        return $this;
    }

    /**
     * Reloads the current page.
     */
    public function reload(): self
    {
        $response = $this->sendMessage('reload', ['waitUntil' => 'load']);
        $this->processVoidResponse($response);

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
     * Resets the assertion count to the original value.
     */
    private function resetAssertions(int $originalCount): void
    {
        if (Assert::getCount() === $originalCount) {
            return;
        }

        $reflector = new ReflectionClass(Assert::class);
        $property = $reflector->getProperty('count');
        $property->setAccessible(true);

        // @phpstan-ignore-next-line
        $property->setValue(Assert::class, $originalCount);
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
