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
        $pages = $this->groupByNavigation($events, $baseUrl);
        $body = $this->renderBody($pages);

        if ($actingAs) {
            $body = "    \$this->actingAs(\\App\\Models\\User::factory()->create());\n\n" . $body;
        }

        $escapedTitle = str_replace("'", "\\'", $title);

        return sprintf("it('%s', function (): void {\n%s\n});", $escapedTitle, $body);
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

            'assertText' => $this->renderAssertText($event),

            default => null,
        };
    }

    private function renderAssertText(RecordedEvent $event): ?string
    {
        $text = $event->text ?? '';

        if ($text === '') {
            return null;
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
