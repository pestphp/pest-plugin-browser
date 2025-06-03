<?php

declare(strict_types=1);

describe('dragAndDrop', function (): void {
    beforeEach(function (): void {
        $this->page = $this->page('/test/frame-tests');
    });

    it('performs drag and drop operations', function (): void {
        // Drag and drop should complete without error
        $this->page->dragAndDrop('#draggable', '#droppable');

        // Verify both elements still exist and are visible (drag and drop completed)
        expect($this->page->isVisible('#draggable'))->toBeTrue();
        expect($this->page->isVisible('#droppable'))->toBeTrue();
    });
});
