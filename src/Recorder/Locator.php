<?php

declare(strict_types=1);

namespace Pest\Browser\Recorder;

use Pest\Browser\Support\Selector;

final readonly class Locator
{
    private function __construct(
        private string $kind,
        private string $body,
        private array $options,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            kind: $data['kind'] ?? 'default',
            body: $data['body'] ?? '',
            options: $data['options'] ?? [],
        );
    }

    public function toSelector(string $testIdAttribute): ?string
    {
        if ($this->kind === 'test-id' && $this->body !== '') {
            return match ($testIdAttribute) {
                'data-test' => '@' . $this->body,
                'data-testid' => '@' . $this->body,
                'id' => '#' . $this->body,
                default => sprintf('[%s="%s"]', $testIdAttribute, $this->body),
            };
        }

        if ($this->kind === 'role') {
            return $this->resolveRoleSelector();
        }

        if ($this->kind === 'text' && $this->body !== '') {
            return $this->body;
        }

        if (in_array($this->kind, ['css', 'default'], true) && $this->body !== '') {
            return $this->body;
        }

        return null;
    }

    private function resolveRoleSelector(): ?string
    {
        $name = trim($this->options['name'] ?? '');

        if ($name === '') {
            return null;
        }

        return match ($this->body) {
            'button', 'link', 'menuitem', 'tab', 'option', 'checkbox', 'radio' => $name,
            'textbox', 'searchbox', 'combobox' => Selector::getByLabelSelector($name, true),
            default => null,
        };
    }
}
