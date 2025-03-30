<?php

declare(strict_types=1);

test('assert attribute doesnt contain', function () {
    $this->visit(playground()->url('/test/interactive-elements'))
        ->assertAttributeDoesntContain('#i-have-data-testid', 'data-testid', 'not-included');
});
