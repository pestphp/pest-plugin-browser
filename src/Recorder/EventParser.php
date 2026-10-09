<?php

declare(strict_types=1);

namespace Pest\Browser\Recorder;

final class EventParser
{
    /**
     * @return RecordedEvent[]
     */
    public function parse(string $jsonlContent): array
    {
        $events = [];

        foreach (explode("\n", $jsonlContent) as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $data = json_decode($line, true);

            if (! is_array($data)) {
                continue;
            }

            $event = RecordedEvent::fromRaw($data);

            if (! is_null($event)) {
                $events[] = $event;
            }
        }

        return $events;
    }
}
