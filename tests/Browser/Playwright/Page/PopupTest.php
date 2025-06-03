<?php

declare(strict_types=1);

describe('Popup', function (): void {

    beforeEach(function (): void {
        $this->page = $this->page('/test/popup-tests');
    });

    it('passes when popup is triggered on button click', function (): void {

        $this->page->waitForLoadState();

        $popupEvent = $this->page->waitForEvent('popup');

        $this->page->getByRole('button', ['name' => 'Trigger Popup'])->click();

        expect($this->page->getByText('This is a popup message.'))->toBe('true');
    });
});
