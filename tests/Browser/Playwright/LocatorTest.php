<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Element;
use Pest\Browser\Playwright\Locator;

describe('Locator', function (): void {
    beforeEach(function (): void {
        $this->page = $this->page(playgroundUrl('/test/element-tests'));
    });

    describe('locator creation', function (): void {
        it('can create a locator from page', function (): void {
            $locator = $this->page->locator('[data-testid="profile-section"]');

            expect($locator)->toBeInstanceOf(Locator::class);
        });

        it('can create a locator using getByTestId', function (): void {
            $locator = $this->page->getByTestId('profile-section');

            expect($locator)->toBeInstanceOf(Locator::class);
        });

        it('can create a locator using getByText', function (): void {
            $locator = $this->page->getByText('Profile Information');

            expect($locator)->toBeInstanceOf(Locator::class);
        });

        it('can create a locator using getByRole', function (): void {
            $locator = $this->page->getByRole('button');

            expect($locator)->toBeInstanceOf(Locator::class);
        });
    });

    describe('state checking methods', function (): void {
        describe('isVisible', function (): void {
            it('returns true for visible elements', function (): void {
                $locator = $this->page->getByTestId('profile-section');

                expect($locator->isVisible())->toBeTrue();
            });

            it('returns false for hidden elements', function (): void {
                $locator = $this->page->getByTestId('hidden-element');

                expect($locator->isVisible())->toBeFalse();
            });
        });

        describe('isHidden', function (): void {
            it('returns false for visible elements', function (): void {
                $locator = $this->page->getByTestId('profile-section');

                expect($locator->isHidden())->toBeFalse();
            });

            it('returns true for hidden elements', function (): void {
                $locator = $this->page->getByTestId('hidden-element');

                expect($locator->isHidden())->toBeTrue();
            });
        });

        describe('isEnabled', function (): void {
            it('returns true for enabled buttons', function (): void {
                $locator = $this->page->getByTestId('enabled-button');

                expect($locator->isEnabled())->toBeTrue();
            });

            it('returns false for disabled buttons', function (): void {
                $locator = $this->page->getByTestId('disabled-button');

                expect($locator->isEnabled())->toBeFalse();
            });
        });

        describe('isDisabled', function (): void {
            it('returns false for enabled buttons', function (): void {
                $locator = $this->page->getByTestId('enabled-button');

                expect($locator->isDisabled())->toBeFalse();
            });

            it('returns true for disabled buttons', function (): void {
                $locator = $this->page->getByTestId('disabled-button');

                expect($locator->isDisabled())->toBeTrue();
            });
        });

        describe('isChecked', function (): void {
            it('returns true for checked checkboxes', function (): void {
                $locator = $this->page->getByTestId('checked-checkbox');

                expect($locator->isChecked())->toBeTrue();
            });

            it('returns false for unchecked checkboxes', function (): void {
                $locator = $this->page->getByTestId('unchecked-checkbox');

                expect($locator->isChecked())->toBeFalse();
            });
        });
    });

    describe('action methods', function (): void {
        describe('click', function (): void {
            it('can click on buttons', function (): void {
                $button = $this->page->getByTestId('click-button');
                $counter = $this->page->getByTestId('click-counter');

                expect($counter->textContent())->toBe('0');

                $button->click();

                expect($counter->textContent())->toBe('1');
            });
        });

        describe('fill', function (): void {
            it('can fill input fields', function (): void {
                $input = $this->page->getByTestId('text-input');

                $input->fill('Hello World');

                expect($input->inputValue())->toBe('Hello World');
            });
        });

        describe('type', function (): void {
            it('can type into input fields', function (): void {
                $input = $this->page->getByTestId('text-input');

                $input->type('Typed text');

                expect($input->inputValue())->toBe('Typed text');
            });
        });

        describe('check and uncheck', function (): void {
            it('can check checkboxes', function (): void {
                $checkbox = $this->page->getByTestId('unchecked-checkbox');

                expect($checkbox->isChecked())->toBeFalse();

                $checkbox->check();

                expect($checkbox->isChecked())->toBeTrue();
            });

            it('can uncheck checkboxes', function (): void {
                $checkbox = $this->page->getByTestId('checked-checkbox');

                expect($checkbox->isChecked())->toBeTrue();

                $checkbox->uncheck();

                expect($checkbox->isChecked())->toBeFalse();
            });
        });

        describe('clear', function (): void {
            it('can clear input fields', function (): void {
                $input = $this->page->getByTestId('prefilled-input');

                expect($input->inputValue())->not()->toBe('');

                $input->clear();

                expect($input->inputValue())->toBe('');
            });
        });
    });

    describe('content retrieval methods', function (): void {
        describe('textContent', function (): void {
            it('can get text content', function (): void {
                $element = $this->page->getByTestId('profile-section');

                expect($element->textContent())->toContain('Profile Section');
            });
        });

        describe('innerText', function (): void {
            it('can get inner text', function (): void {
                $element = $this->page->getByTestId('profile-section');

                expect($element->innerText())->toContain('Profile Section');
            });
        });

        describe('innerHTML', function (): void {
            it('can get inner HTML', function (): void {
                $element = $this->page->getByTestId('profile-section');

                expect($element->innerHTML())->toContain('<');
            });
        });

        describe('inputValue', function (): void {
            it('can get input value', function (): void {
                $input = $this->page->getByTestId('prefilled-input');

                expect($input->inputValue())->not()->toBe('');
            });
        });

        describe('getAttribute', function (): void {
            it('can get attribute values', function (): void {
                $element = $this->page->getByTestId('profile-section');

                expect($element->getAttribute('data-testid'))->toBe('profile-section');
            });
        });
    });

    describe('locator chaining methods', function (): void {
        describe('first', function (): void {
            it('can get first element from multiple matches', function (): void {
                $buttons = $this->page->locator('button');
                $firstButton = $buttons->first();

                expect($firstButton)->toBeInstanceOf(Locator::class);
            });
        });

        describe('last', function (): void {
            it('can get last element from multiple matches', function (): void {
                $buttons = $this->page->locator('button');
                $lastButton = $buttons->last();

                expect($lastButton)->toBeInstanceOf(Locator::class);
            });
        });

        describe('nth', function (): void {
            it('can get nth element from multiple matches', function (): void {
                $buttons = $this->page->locator('button');
                $secondButton = $buttons->nth(1);

                expect($secondButton)->toBeInstanceOf(Locator::class);
            });
        });

        describe('locator chaining', function (): void {
            it('can chain locators', function (): void {
                $nestedElement = $this->page->getByTestId('profile-section')->getByText('Name');

                expect($nestedElement)->toBeInstanceOf(Locator::class);
            });
        });

        describe('filter', function (): void {
            it('can filter locators', function (): void {
                $buttons = $this->page->locator('button');
                $filteredButtons = $buttons->filter('[data-testid="click-button"]');

                expect($filteredButtons)->toBeInstanceOf(Locator::class);
            });
        });
    });

    describe('element handle conversion', function (): void {
        describe('elementHandle', function (): void {
            it('can get element handle from locator', function (): void {
                $locator = $this->page->getByTestId('profile-section');
                $element = $locator->elementHandle();

                expect($element)->toBeInstanceOf(Element::class);
            });

            it('returns null for non-existent elements', function (): void {
                $locator = $this->page->getByTestId('non-existent-element');
                $element = $locator->elementHandle();

                expect($element)->toBeNull();
            });
        });
    });

    describe('count method', function (): void {
        it('can count matching elements', function (): void {
            $buttons = $this->page->locator('button');

            expect($buttons->count())->toBeGreaterThan(0);
        });

        it('returns 0 for non-existent elements', function (): void {
            $nonExistent = $this->page->locator('.non-existent-class');

            expect($nonExistent->count())->toBe(0);
        });
    });
});
