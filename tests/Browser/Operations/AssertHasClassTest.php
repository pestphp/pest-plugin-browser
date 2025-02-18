<?php

declare(strict_types=1);

describe('assert has class', function () {
    it('asserts that the element has a single class', function () {
        $this->visit('/test/interactive-elements')
            ->assertHasClass('#div-1', 'class-1');
    });

    it('asserts that the element has multiple classes', function () {
        $this->visit('/test/interactive-elements')
            ->assertHasClass('#div-3', 'class-1 selected class-3');
    });

    it('asserts that the element has a regex-based class match', function () {
        $this->visit('/test/interactive-elements')
            ->assertHasClass('#div-3', '/(^|\s)selected(\s|$)/');
    });

    it('asserts that the list of elements located matches the corresponding list of expected class', function () {
        $this->visit('/test/interactive-elements')
            ->assertHasClass('ul > .component', ['component', 'component selected', 'component']);
    });
});
