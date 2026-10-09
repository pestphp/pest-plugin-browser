<?php

declare(strict_types=1);

namespace Pest\Browser\Recorder;

final readonly class RecordedEvent
{
    public function __construct(
        public string $type,
        public ?string $url = null,
        public ?array $locator = null,
        public ?string $text = null,
        public ?string $key = null,
        public ?string $selectValue = null,
    ) {}

    public static function fromRaw(array $data): ?self
    {
        $type = $data['name'] ?? null;

        if (! is_string($type)) {
            return null;
        }

        $selectValue = null;

        if (isset($data['options'])) {
            $option = $data['options'][0] ?? null;
            $selectValue = is_array($option)
                ? ($option['value'] ?? $option['label'] ?? null)
                : (is_string($option) ? $option : null);
        }

        return new self(
            type: $type,
            url: $data['url'] ?? null,
            locator: $data['locator'] ?? null,
            text: $data['text'] ?? $data['value'] ?? null,
            key: $data['key'] ?? null,
            selectValue: $selectValue,
        );
    }
}
