<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Livewire\Component;
use Pest\Browser\Api\Livewire;

beforeEach(function (): void {
    if (! file_exists(base_path('.env'))) {
        copy(base_path('.env.example'), base_path('.env'));
    }

    Artisan::call('key:generate', [
        '--force' => true,
    ]);
});

it('may visit an anonymous component', function (bool $useClassName): void {
    $component = new class extends Component
    {
        public int $counter = 0;

        public function increment(): void
        {
            $this->counter++;
        }

        public function render(): string
        {
            return <<<'HTML'
                <div>
                    <h1>Hello, World!</h1>
                    <button wire:click="increment">Increment</button>
                    <span>{{ $counter }}</span>
                </div>
            HTML;
        }
    };

    $page = Livewire::test($useClassName ? $component::class : $component, [
        'counter' => 0,
    ]);

    $page
        ->assertSee('Hello, World!')
        ->click('Increment')
        ->assertSee(1)
        ->assertSet('counter', 1)
        ->click('Increment')
        ->assertSee(2)
        ->assertSet('counter', 2);
})->with([
    'instance' => false,
    'class name' => true,
]);
