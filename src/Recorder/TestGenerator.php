<?php

declare(strict_types=1);

namespace Pest\Browser\Recorder;

final class TestGenerator
{
    public function __construct(
        private readonly string $testIdAttribute,
    ) {}

    /**
     * @param RecordedEvent[] $events
     */
    public function generate(array $events, string $title, string $baseUrl, bool $actingAs = false): string
    {
        if (! $actingAs) {
            [$events, $actingAs] = $this->stripLoginSequence($events);
        }

        $pages = $this->groupByNavigation($events, $baseUrl);
        $body = $this->renderBody($pages);

        if ($actingAs) {
            $body = "    \$this->actingAs(\\App\\Models\\User::factory()->create());\n\n" . $body;
        }

        $escapedTitle = str_replace("'", "\\'", $title);

        return sprintf("it('%s', function (): void {\n%s\n});", $escapedTitle, $body);
    }

    /**
     * Detect a login form (email + password fills) and strip it from the recorded events.
     * Returns the cleaned event list and whether a login sequence was found.
     *
     * @param RecordedEvent[] $events
     * @return array{0: RecordedEvent[], 1: bool}
     */
    private function stripLoginSequence(array $events): array
    {
        $passwordIdx = null;

        foreach ($events as $i => $event) {
            if ($event->type !== 'fill') {
                continue;
            }

            $selector = $this->resolveSelector($event);

            if ($selector !== null && str_contains(strtolower($selector), 'password')) {
                $passwordIdx = $i;
                break;
            }
        }

        if ($passwordIdx === null) {
            return [$events, false];
        }

        $emailIdx = null;

        for ($i = $passwordIdx - 1; $i >= 0; $i--) {
            if ($events[$i]->type === 'fill') {
                $emailIdx = $i;
                break;
            }
        }

        $start = $emailIdx ?? $passwordIdx;

        // Also remove the "Log in" link click that precedes the email field
        if ($emailIdx !== null && $start > 0 && $events[$start - 1]->type === 'click') {
            $start--;
        }

        // Remove up to 2 clicks after the password fill (remember-me checkbox + submit button)
        $end = $passwordIdx;
        $clickCount = 0;

        while (isset($events[$end + 1]) && $events[$end + 1]->type === 'click' && $clickCount < 2) {
            $end++;
            $clickCount++;
        }

        array_splice($events, $start, $end - $start + 1);

        return [$events, true];
    }

    private function resolveSelector(RecordedEvent $event): ?string
    {
        if ($event->locator === null) {
            return null;
        }

        return Locator::fromArray($event->locator)->toSelector($this->testIdAttribute);
    }

    /**
     * @param RecordedEvent[] $events
     * @return array<int, array{path: string, events: RecordedEvent[]}>
     */
    private function groupByNavigation(array $events, string $baseUrl): array
    {
        $pages = [];
        $current = null;

        foreach ($events as $event) {
            if ($event->type === 'navigate') {
                if (! is_null($current)) {
                    $pages[] = $current;
                }

                $current = [
                    'path' => $this->toPath($event->url ?? '/', $baseUrl),
                    'events' => [],
                ];

                continue;
            }

            if (is_null($current)) {
                $current = ['path' => '/', 'events' => []];
            }

            $current['events'][] = $event;
        }

        if (! is_null($current)) {
            $pages[] = $current;
        }

        return $pages;
    }

    /**
     * @param array<int, array{path: string, events: RecordedEvent[]}> $pages
     */
    private function renderBody(array $pages): string
    {
        $blocks = [];

        foreach ($pages as $page) {
            $lines = [sprintf("    visit('%s')", $page['path'])];

            foreach ($page['events'] as $event) {
                $action = $this->renderAction($event);

                if (! is_null($action)) {
                    $lines[] = '        ->' . $action;
                }
            }

            $lastIndex = count($lines) - 1;
            $lines[$lastIndex] .= ';';

            $blocks[] = implode("\n", $lines);
        }

        return implode("\n\n", $blocks);
    }

    private function renderAction(RecordedEvent $event): ?string
    {
        $selector = ! is_null($event->locator)
            ? Locator::fromArray($event->locator)->toSelector($this->testIdAttribute)
            : null;

        return match ($event->type) {
            'click' => ! is_null($selector)
                ? sprintf("click('%s')", $this->escape($selector))
                : null,

            'fill' => ! is_null($selector)
                ? sprintf("fill('%s', '%s')", $this->escape($selector), $this->escape($event->text ?? ''))
                : null,

            'selectOption' => ! is_null($selector)
                ? sprintf("select('%s', '%s')", $this->escape($selector), $this->escape($event->selectValue ?? ''))
                : null,

            'check' => ! is_null($selector)
                ? sprintf("check('%s')", $this->escape($selector))
                : null,

            'uncheck' => ! is_null($selector)
                ? sprintf("uncheck('%s')", $this->escape($selector))
                : null,

            'assertVisible' => ! is_null($selector)
                ? sprintf("assertVisible('%s')", $this->escape($selector))
                : null,

            'assertText' => $this->renderAssertText($event, $selector),

            default => null,
        };
    }

    private function renderAssertText(RecordedEvent $event, ?string $selector): ?string
    {
        $text = $event->text ?? '';

        if ($text === '') {
            return null;
        }

        if (! is_null($selector)) {
            return sprintf("assertSeeIn('%s', '%s')", $this->escape($selector), $this->escape($text));
        }

        return sprintf("assertSee('%s')", $this->escape($text));
    }

    private function toPath(string $url, string $baseUrl): string
    {
        $base = rtrim($baseUrl, '/');

        if (str_starts_with($url, $base)) {
            return substr($url, strlen($base)) ?: '/';
        }

        return $url;
    }

    private function escape(string $value): string
    {
        return str_replace(['\\', "'"], ['\\\\', "\\'"], $value);
    }
}
