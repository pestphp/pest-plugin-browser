<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

use Generator;
use Pest\Browser\Execution;
use Pest\Browser\Support\ImageDiffView;
use Pest\Browser\Support\JavaScriptSerializer;
use Pest\Browser\Support\Screenshot;
use Pest\Browser\Support\Selector;
use Pest\Browser\Support\Shell;
use Pest\TestSuite;
use PHPUnit\Framework\ExpectationFailedException;
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
        $url = Execution::instance()->waitForExpectation(
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
     * Waits for a JavaScript function to return true.
     *
     * @param  mixed  $arg  Optional argument to pass to the function
     */
    public function waitForFunction(string $content, mixed $arg = null): self
    {
        $params = [
            'expression' => $content,
            'arg' => JavaScriptSerializer::serializeArgument($arg),
        ];

        Client::instance()->execute(
            $this->guid,
            'waitForFunction',
            $params
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
     * Adds a script tag to the page.
     */
    public function addStyleTag(string $content): self
    {
        $response = $this->sendMessage('addStyleTag', ['content' => $content]);
        $this->processVoidResponse($response);

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
     * Sets the viewport size and resizes the page.
     */
    public function setViewportSize(int $width, int $height): self
    {
        $viewportSize = ['viewportSize' => ['width' => $width, 'height' => $height]];

        $response = $this->sendMessage('setViewportSize', $viewportSize);

        $this->processVoidResponse($response);

        return $this;
    }

    /**
     * Returns the viewport size.
     *
     * @return array{width: int, height: int}
     */
    public function viewportSize(): array
    {
        /** @var array{width: int, height: int} $result */
        $result = $this->evaluate('() => ({ width: window.innerWidth, height: window.innerHeight })');

        return $result;
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
    public function screenshot(bool $fullPage = true, ?string $filename = null): ?string
    {
        $binary = $this->screenshotBinary($fullPage);

        if ($binary === null) {
            return null;
        }

        return Screenshot::save($binary, $filename);
    }

    /**
     * Make screenshot of a specific element.
     */
    public function screenshotElement(string $selector, ?string $filename = null): string
    {
        $locator = $this->locator($selector);
        $binary = $locator->screenshot();

        return Screenshot::save($binary, $filename);
    }

    /**
     * Get the console logs from the page, if any.
     *
     * @return array<int, array{message: string}>
     */
    public function consoleLogs(): array
    {
        $consoleLogs = $this->evaluate('window.__pestBrowser.consoleLogs || []');

        /** @var array<int, array{message: string}> $consoleLogs */

        return $consoleLogs;
    }

    /**
     * Get the broken images from the page, if any.
     *
     * @return array<int, string>
     */
    public function brokenImages(): array
    {
        $brokenImages = $this->evaluate(<<<'JS'
            () => {
                return Array.from(document.images)
                    .filter(img => img.complete && img.naturalWidth === 0)
                    .map(img => img.src);
            }
            JS);

        /** @var array<int, string> $brokenImages */
        return $brokenImages;

    }

    /**
     * Get the JavaScript errors from the page, if any.
     *
     * @return array<int, array{message: string}>
     */
    public function javaScriptErrors(): array
    {
        $jsErrors = $this->evaluate('window.__pestBrowser.jsErrors || []');

        /** @var array<int, array{message: string}> $jsErrors */

        return $jsErrors;
    }

    /**
     * Make a screenshot of the page and compare it with the expected one.
     *
     * @throws ExpectationFailedException
     */
    public function expectScreenshot(bool $fullPage, bool $openDiff): void
    {
        $actualImageBlob = $this->screenshotBinary($fullPage);
        assert(is_string($actualImageBlob), 'Unable to screenshot');

        try {
            expect($actualImageBlob)->toMatchSnapshot();
        } catch (ExpectationFailedException) {
            [$snapshotName, $expectedImageBlob] = TestSuite::getInstance()->snapshots->get();

            $response = Client::instance()->execute(
                $this->guid,
                'expectScreenshot',
                [
                    ...$this->screenshotOptions($fullPage),
                    'expected' => $expectedImageBlob,
                    'timeout' => 30000,
                    'isNot' => false,
                    'comparisonMethod' => 'pixelmatch',
                    'threshold' => 0.3,
                    'maxDiffPixels' => 300,
                    'maxDiffPixelRatio' => 0.01,
                    'detectAntialiasing' => true,
                    'forceSameDimensions' => true,
                ]
            );

            $snapshotName = pathinfo($snapshotName, PATHINFO_FILENAME);
            /** @var array{result: array{diff: string|null}} $message */
            foreach ($response as $message) {
                if (isset($message['result']['diff'])) {
                    $this->createImageDiffView(
                        $snapshotName,
                        $expectedImageBlob,
                        $actualImageBlob,
                        $message['result']['diff'],
                        $openDiff
                    );

                    throw new ExpectationFailedException(<<<'EOT'
                        Screenshot does not match the last one.
                          - Expected? Update the snapshots with [--update-snapshots].
                          - Not expected? Re-run the test with [--diff] to see the differences.
                        EOT
                    );
                }
            }

            $this->createImageDiffView(
                $snapshotName,
                $expectedImageBlob,
                $actualImageBlob,
                ImageDiffView::missingImage(),
                $openDiff,
            );

            throw new ExpectationFailedException(<<<'EOT'
                Screenshot does not match the last one.
                  - Expected? Update the snapshots with [--update-snapshots].
                EOT,
            );
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
     * Screenshots the page and returns the binary data.
     */
    private function screenshotBinary(bool $fullPage = true): ?string
    {
        $response = Client::instance()->execute(
            $this->guid,
            'screenshot',
            $this->screenshotOptions($fullPage)
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
            'keyboardDown',
            'keyboardUp',
            'setViewportSize',
            'viewportSize',
        ];

        return in_array($method, $pageLevelOperations, true);
    }

    /**
     * @return array<string, mixed>
     */
    private function screenshotOptions(bool $fullPage = true): array
    {
        return [
            'type' => 'png',
            'fullPage' => $fullPage,
            'caret' => 'hide',
            'animations' => 'disabled',
            'scale' => 'css',
        ];
    }

    /**
     * Create an HTML view for the image diff.
     */
    private function createImageDiffView(
        string $snapshotName,
        string $expectedImageBlob,
        string $actualImageBlob,
        string $diff,
        bool $openDiff
    ): void {
        $imageDiffViewDir = Screenshot::dir().'/ImageDiffView';

        if (is_dir($imageDiffViewDir) === false) {
            mkdir($imageDiffViewDir, 0755, true);
        }

        $imageDiffViewPath = $imageDiffViewDir.'/'.$snapshotName.'.html';

        file_put_contents($imageDiffViewPath, ImageDiffView::generate(
            $expectedImageBlob,
            $actualImageBlob,
            $diff,
            test()->name() // @phpstan-ignore-line
        ));

        if ($openDiff) {
            Shell::open($imageDiffViewPath);
        }
    }
}
