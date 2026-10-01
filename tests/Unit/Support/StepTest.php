<?php

declare(strict_types=1);

use Pest\Browser\Support\Step;

it('formats a method call as a step title', function (): void {
    expect(Step::title('assertSee', ['Welcome']))->toBe("assertSee('Welcome')")
        ->and(Step::title('type', ['email', 'taylor@laravel.com']))->toBe("type('email', 'taylor@laravel.com')")
        ->and(Step::title('assertCount', ['li', 3]))->toBe("assertCount('li', 3)")
        ->and(Step::title('check', ['remember', true]))->toBe("check('remember', true)")
        ->and(Step::title('select', ['tags', ['php', 'js']]))->toBe('select(\'tags\', ["php","js"])')
        ->and(Step::title('assertScript', ['x', null]))->toBe("assertScript('x', null)")
        ->and(Step::title('screenshot', []))->toBe('screenshot()');
});

it('truncates long arguments', function (): void {
    $title = Step::title('assertSee', [str_repeat('a', 100)]);

    expect(mb_strlen($title))->toBe(mb_strlen('assertSee()') + 60)
        ->and($title)->toEndWith('…)');
});
