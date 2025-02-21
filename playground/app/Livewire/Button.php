<?php

declare(strict_types=1);

namespace App\Livewire;

use AllowDynamicProperties;
use Illuminate\Support\Arr;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * @property string[] $events
 * @property string $label
 */
#[AllowDynamicProperties]
final class Button extends Component
{
    public $counter = 0;

    public $flavor;

    #[Computed]
    public function events()
    {
        return Arr::wrap(match ($this->flavor) {
            'control' => ['click.cmd', 'click.ctrl'],
            'double' => 'dblclick',
            'hold' => null,
            'right' => 'contextmenu.prevent',
            default => 'click',
        });
    }

    #[Computed]
    public function label()
    {
        return match ($this->flavor) {
            'control' => 'ctrl|cmd + click me',
            'double' => 'Double click me',
            'hold' => 'Click and hold me',
            'point' => 'Absolutely click me',
            'right' => 'Right click me',
            default => 'Click me',
        };
    }

    public function handle()
    {
        $this->counter++;

        $message = match ($this->flavor) {
            'control' => 'ctrl|cmd clicked!',
            'double' => 'Double clicked!',
            'hold' => 'Free hug!',
            'point' => 'Absolutely clicked!',
            'right' => 'Right clicked!',
            default => 'Single clicked!',
        };

        $this->label = "{$message} ({$this->counter})";
    }

    public function render()
    {
        return view('livewire.button');
    }
}
