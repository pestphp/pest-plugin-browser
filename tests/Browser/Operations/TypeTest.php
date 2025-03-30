<?php

declare(strict_types=1);

test('types some text into the input', function (): void {
    $this->visit('/test/form-inputs')
        ->type('#email', 'jane.doe@pestphp.com')
        ->assertInputValue('#email', 'jane.doe@pestphp.com');
});
