<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Element;

describe('Frame', function () {
    beforeEach(function () {
        $this->page = $this->page(playgroundUrl('/test/selector-tests'));
    });

    describe('getByTestId', function () {
        it('finds an element by test ID', function () {
            $element = $this->page->getByTestId('profile-section');

            expect($element)->toBeInstanceOf(Element::class);
            expect($element->isVisible())->toBeTrue();
        });

        it('finds a nested element by test ID', function () {
            $element = $this->page->getByTestId('user-email');

            expect($element)->toBeInstanceOf(Element::class);
            expect($element->isVisible())->toBeTrue();
        });

        it('returns null for non-existent test ID', function () {
            $element = $this->page->getByTestId('non-existent-id');

            expect($element)->toBeNull();
        });
    });

    describe('getByRole', function () {
        it('finds an element by role with name option', function () {
            $element = $this->page->getByRole('button', ['name' => 'Save']);

            expect($element)->toBeInstanceOf(Element::class);
            expect($element->isVisible())->toBeTrue();
        });

        it('finds a checkbox by role with name option', function () {
            $element = $this->page->getByRole('checkbox', ['name' => 'Remember Me']);

            expect($element)->toBeInstanceOf(Element::class);
        });

        it('returns null for non-existent role', function () {
            $element = $this->page->getByRole('tab', ['name' => 'Non-existent']);

            expect($element)->toBeNull();
        });
    });

    describe('getByLabel', function () {
        it('finds an input element by its associated label', function () {
            $element = $this->page->getByLabel('Username');

            expect($element)->toBeInstanceOf(Element::class);
            expect($element->getAttribute('value'))->toBe('johndoe');
        });

        it('finds a password input by its label', function () {
            $element = $this->page->getByLabel('Password');

            expect($element)->toBeInstanceOf(Element::class);
            expect($element->getAttribute('type'))->toBe('password');
        });

        it('returns null for non-existent label', function () {
            $element = $this->page->getByLabel('Non-existent Label');

            expect($element)->toBeNull();
        });
    });

    describe('getByPlaceholder', function () {
        it('finds an input element by placeholder text', function () {
            $element = $this->page->getByPlaceholder('Search...');

            expect($element)->toBeInstanceOf(Element::class);
            expect($element->getAttribute('type'))->toBe('text');
        });

        it('finds a textarea by placeholder text', function () {
            $element = $this->page->getByPlaceholder('Enter your comments here');

            expect($element)->toBeInstanceOf(Element::class);
            expect($element->isVisible())->toBeTrue();
        });

        it('returns null for non-existent placeholder', function () {
            $element = $this->page->getByPlaceholder('Non-existent Placeholder');

            expect($element)->toBeNull();
        });

        it('finds an element with exact matching', function () {
            $element = $this->page->getByPlaceholder('Search...', true);

            expect($element)->toBeInstanceOf(Element::class);
        });
    });

    describe('getByText', function () {
        it('finds an element by its text content', function () {
            $element = $this->page->getByText('This is a simple paragraph');

            expect($element)->toBeInstanceOf(Element::class);
            expect($element->isVisible())->toBeTrue();
        });

        it('finds a button by its text content', function () {
            $element = $this->page->getByText('Click Me Button');

            expect($element)->toBeInstanceOf(Element::class);
        });

        it('returns null for non-existent text', function () {
            $element = $this->page->getByText('Non-existent Text Content');

            expect($element)->toBeNull();
        });

        it('finds an element with exact matching', function () {
            $element = $this->page->getByText('This is a special span element', true);

            expect($element)->toBeInstanceOf(Element::class);
        });

        it('finds partial text without exact matching', function () {
            $element = $this->page->getByText('special span');

            expect($element)->toBeInstanceOf(Element::class);
        });
    });

    describe('getByAltText', function () {
        it('finds an image by its alt text', function () {
            $element = $this->page->getByAltText('Pest Logo');

            expect($element)->toBeInstanceOf(Element::class);
            expect($element->isVisible())->toBeTrue();
        });

        it('finds another image by its alt text', function () {
            $element = $this->page->getByAltText('Another Image');

            expect($element)->toBeInstanceOf(Element::class);
        });

        it('returns null for non-existent alt text', function () {
            $element = $this->page->getByAltText('Non-existent Alt Text');

            expect($element)->toBeNull();
        });

        it('finds an element with exact matching', function () {
            $element = $this->page->getByAltText('Profile Picture', true);

            expect($element)->toBeInstanceOf(Element::class);
        });
    });

    describe('getByTitle', function () {
        it('finds an element by its title attribute', function () {
            $element = $this->page->getByTitle('Info Button');

            expect($element)->toBeInstanceOf(Element::class);
            expect($element->isVisible())->toBeTrue();
        });

        it('finds a link by its title attribute', function () {
            $element = $this->page->getByTitle('Help Link');

            expect($element)->toBeInstanceOf(Element::class);
        });

        it('returns null for non-existent title', function () {
            $element = $this->page->getByTitle('Non-existent Title');

            expect($element)->toBeNull();
        });

        it('finds an element with exact matching', function () {
            $element = $this->page->getByTitle('User\'s Name', true);

            expect($element)->toBeInstanceOf(Element::class);
        });
    });

    describe('combining selectors', function () {
        it('can find elements using multiple methods in sequence', function () {
            // First get the profile section by testId
            $profileSection = $this->page->getByTestId('user-profile');
            expect($profileSection)->toBeInstanceOf(Element::class);

            // Then find a button element with role and aria-label within it
            $button = $this->page->getByRole('button', ['name' => 'Edit Profile']);
            expect($button)->toBeInstanceOf(Element::class);

            // Verify it has the right content
            expect($button->isVisible())->toBeTrue();
        });
    });
});
