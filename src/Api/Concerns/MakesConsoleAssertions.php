<?php

declare(strict_types=1);

namespace Pest\Browser\Api\Concerns;

use InvalidArgumentException;
use Pest\Browser\Api\Webpage;
use Pest\Browser\Enums\AccessibilityIssueLevel;
use Pest\Browser\Playwright\Playwright;
use Pest\Browser\Support\AccessibilityFormatter;

/**
 * @mixin Webpage
 *
 * @phpstan-import-type Violations from AccessibilityFormatter
 */
trait MakesConsoleAssertions
{
    /**
     * Asserts there are no console logs or JavaScript errors on the page.
     */
    public function assertNoSmoke(): Webpage
    {
        $this->assertNoConsoleLogs();
        $this->assertNoJavaScriptErrors();

        return $this;
    }

    /**
     * Asserts there are no broken images on the page.
     */
    public function assertNoBrokenImages(): Webpage
    {
        $this->page->waitForLoadState('load');

        $brokenImages = $this->page->brokenImages();

        expect($brokenImages)->toBeEmpty(sprintf(
            'Expected no broken images on the page initially with the url [%s], but found %s: %s',
            $this->initialUrl,
            count($brokenImages),
            implode(', ', $brokenImages),
        ));

        return $this;
    }

    /**
     * Asserts there are no missing images on the page.
     */
    public function assertNoMissingImages(): Webpage
    {
        return $this->assertNoBrokenImages();
    }

    /**
     * Asserts there are no console logs on the page.
     */
    public function assertNoConsoleLogs(): Webpage
    {
        $consoleLogs = $this->page->consoleLogs();

        expect($consoleLogs)->toBeEmpty(sprintf(
            'Expected no console logs on the page initially with the url [%s], but found %s: %s',
            $this->initialUrl,
            count($consoleLogs),
            implode(', ', array_map(fn (array $log) => $log['message'], $consoleLogs)),
        ));

        return $this;
    }

    /**
     * Asserts there are no console messages at given levels on the page.
     *
     * @param  array<int, string>|string  $levels  Console message level(s) to check for (debug, info, error, warning).
     */
    public function assertNoConsoleMessages(array|string $levels = ['info', 'error', 'warning']): Webpage
    {
        if (is_string($levels)) {
            $levels = [$levels];
        }
        $messages = $this->page->consoleMessages($levels);

        expect($messages)->toBeEmpty(sprintf(
            'Expected no console messages at levels [%s] on the page initially with the url [%s], but found %d: %s',
            implode(', ', $levels),
            $this->initialUrl,
            count($messages),
            implode(', ', array_map(fn (array $log): string => "{$log['level']}: {$log['message']}", $messages)),
        ));

        return $this;
    }

    /**
     * Asserts there are no JavaScript errors on the page.
     */
    public function assertNoJavaScriptErrors(): Webpage
    {
        $javaScriptErrors = $this->page->javaScriptErrors();

        expect($javaScriptErrors)->toBeEmpty(sprintf(
            'Expected no JavaScript errors on the page initially with the url [%s], but found %s: %s',
            $this->initialUrl,
            count($javaScriptErrors),
            implode(', ', array_map(fn (array $log) => $log['message'], $javaScriptErrors)),
        ));

        return $this;
    }

    /**
     * Asserts the accessibility of the page.
     */
    public function assertNoAccessibilityIssues(int $level = 1): Webpage
    {
        $this->page->waitForLoadState('networkidle');
        $this->page->waitForFunction('document.readyState === "complete"');

        $level = AccessibilityIssueLevel::tryFromLevel($level);

        if (! $level instanceof AccessibilityIssueLevel) {
            throw new InvalidArgumentException(
                'The accessibility issue level must be between [0] (critical) and [3] (minor).',
            );
        }

        /** @var Violations|null $violations */
        $violations = Playwright::usingTimeout(5_000, fn () => $this->page->evaluate('async () => ((await window.axe.run()).violations)'));

        if (! is_array($violations)) {
            $violations = [];
        }

        $violations = array_filter($violations, function (array $violation) use ($level): bool {
            $violationImpact = $violation['impact'] ?? null;

            $violationRank = is_string($violationImpact) ? AccessibilityIssueLevel::from($violationImpact)->level() : -1;

            return $violationRank <= $level->level();
        });

        $report = AccessibilityFormatter::format($violations);

        expect($violations)->toBeEmpty($report);

        return $this;
    }
}
