<?php

declare(strict_types=1);

namespace Pest\Browser\Recorder;

final class EventSanitizer
{
    private const array SUPPORTED_TYPES = [
        'navigate',
        'click',
        'fill',
        'selectOption',
        'check',
        'uncheck',
        'assertVisible',
        'assertText',
    ];

    public function __construct(
        private readonly string $testIdAttribute,
    ) {}

    /**
     * @param RecordedEvent[] $events
     * @return RecordedEvent[]
     */
    public function sanitize(array $events): array
    {
        $events = $this->dropUnsupported($events);
        $events = $this->dropRedundantClicks($events);
        $events = $this->deduplicateFills($events);

        return array_values($events);
    }

    /**
     * @param RecordedEvent[] $events
     * @return RecordedEvent[]
     */
    private function dropUnsupported(array $events): array
    {
        return array_values(array_filter($events, function (RecordedEvent $event): bool {
            if (! in_array($event->type, self::SUPPORTED_TYPES, true)) {
                return false;
            }

            if ($event->type === 'navigate') {
                return is_string($event->url);
            }

            if (is_null($event->locator)) {
                return false;
            }

            return ! is_null(Locator::fromArray($event->locator)->toSelector($this->testIdAttribute));
        }));
    }

    /**
     * @param RecordedEvent[] $events
     * @return RecordedEvent[]
     */
    private function dropRedundantClicks(array $events): array
    {
        $result = [];

        foreach ($events as $index => $event) {
            if ($event->type === 'click') {
                $next = $events[$index + 1] ?? null;

                if (
                    ! is_null($next)
                    && $next->type === 'fill'
                    && $this->resolveSelector($event) === $this->resolveSelector($next)
                ) {
                    continue;
                }
            }

            $result[] = $event;
        }

        return $result;
    }

    /**
     * @param RecordedEvent[] $events
     * @return RecordedEvent[]
     */
    private function deduplicateFills(array $events): array
    {
        $lastIndex = [];

        foreach ($events as $index => $event) {
            if ($event->type === 'fill') {
                $selector = $this->resolveSelector($event);
                if (! is_null($selector)) {
                    $lastIndex[$selector] = $index;
                }
            }
        }

        $result = [];

        foreach ($events as $index => $event) {
            if ($event->type === 'fill') {
                $selector = $this->resolveSelector($event);
                if (! is_null($selector) && $lastIndex[$selector] !== $index) {
                    continue;
                }
            }

            $result[] = $event;
        }

        return $result;
    }

    private function resolveSelector(RecordedEvent $event): ?string
    {
        if (is_null($event->locator)) {
            return null;
        }

        return Locator::fromArray($event->locator)->toSelector($this->testIdAttribute);
    }
}
