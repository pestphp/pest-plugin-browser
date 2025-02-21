<?php

declare(strict_types=1);

test('appends text to the input', function (): void {
    $this->visit('/test/form-inputs')
        ->type('#email', 'jane.doe')
        ->assertInputValue('#email', 'jane.doe')
        ->append('#email', '@pestphp.com')
        ->assertInputValue('#email', 'jane.doe@pestphp.com');
});
