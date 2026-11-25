<?php

declare(strict_types=1);

use Illuminate\Container\Container;
use Pest\Browser\Drivers\ContainerInstances;

it('replaces an instance that holds a reference to itself with a different one that does too', function (): void {
    $container = new Container();
    $instances = new ReflectionProperty(Container::class, 'instances');
    $instances->setValue($container, new ContainerInstances([], built: true));

    $first = ['name' => 'tree'];
    $first['self'] = &$first;
    $second = ['name' => 'tree'];
    $second['self'] = &$second;

    $container->instance('recursive', $first);
    $container->instance('recursive', $second);

    $second['name'] = 'changed';

    expect($container->make('recursive')['self']['name'])->toBe('changed');
});
