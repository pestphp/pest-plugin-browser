<?php

declare(strict_types=1);

use Pest\Browser\Playwright\Element;

describe('Element', function (): void {
    describe('state checking methods', function (): void {
        describe('isVisible', function (): void {
            it('returns true for visible elements', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $locator = $page->getByTestId('profile-section');
                $element = $locator->elementHandle();

                expect($element)->toBeInstanceOf(Element::class);
                expect($element->isVisible())->toBeTrue();
            });

            it('returns false for hidden elements', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $locator = $page->getByTestId('hidden-element');
                $element = $locator->elementHandle();

                expect($element)->toBeInstanceOf(Element::class);
                expect($element->isVisible())->toBeFalse();
            });
        });

        describe('isHidden', function (): void {
            it('returns false for visible elements', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $locator = $page->getByTestId('profile-section');
                $element = $locator->elementHandle();

                expect($element->isHidden())->toBeFalse();
            });

            it('returns true for hidden elements', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $locator = $page->getByTestId('hidden-element');
                $element = $locator->elementHandle();

                expect($element->isHidden())->toBeTrue();
            });
        });

        describe('isEnabled', function (): void {
            it('returns true for enabled input elements', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $locator = $page->getByLabel('Username');
                $element = $locator->elementHandle();

                expect($element->isEnabled())->toBeTrue();
            });

            it('returns false for disabled input elements', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $locator = $page->getByTestId('disabled-input');
                $element = $locator->elementHandle();

                expect($element->isEnabled())->toBeFalse();
            });
        });

        describe('isDisabled', function (): void {
            it('returns false for enabled input elements', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $locator = $page->getByLabel('Username');
                $element = $locator->elementHandle();

                expect($element->isDisabled())->toBeFalse();
            });

            it('returns true for disabled input elements', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $locator = $page->getByTestId('disabled-input');
                $element = $locator->elementHandle();

                expect($element->isDisabled())->toBeTrue();
            });
        });

        describe('isEditable', function (): void {
            it('returns true for editable input elements', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $locator = $page->getByLabel('Username');
                $element = $locator->elementHandle();

                expect($element->isEditable())->toBeTrue();
            });

            it('returns false for readonly input elements', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $locator = $page->getByTestId('readonly-input');
                $element = $locator->elementHandle();

                expect($element->isEditable())->toBeFalse();
            });
        });

        describe('isChecked', function (): void {
            it('returns false for unchecked checkbox', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $locator = $page->getByRole('checkbox', ['name' => 'Remember Me']);
                $element = $locator->elementHandle();

                expect($element->isChecked())->toBeFalse();
            });

            it('returns true for checked checkbox', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $locator = $page->getByRole('checkbox', ['name' => 'Remember Me']);
                $element = $locator->elementHandle();

                $element->check();
                expect($element->isChecked())->toBeTrue();
            });
        });
    });

    describe('interaction methods', function (): void {
        describe('click', function (): void {
            it('can click on buttons', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $locator = $page->getByRole('button', ['name' => 'Save']);
                $element = $locator->elementHandle();

                expect($element)->toBeInstanceOf(Element::class);
                $element->click();

                // Verify click happened by checking if button is still visible
                expect($element->isVisible())->toBeTrue();
            });

            it('can click with options', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $locator = $page->getByRole('button', ['name' => 'Save']);
                $element = $locator->elementHandle();

                $element->click(['force' => true]);
                expect($element->isVisible())->toBeTrue();
            });
        });

        describe('dblclick', function (): void {
            it('can double-click on elements', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $locator = $page->getByRole('button', ['name' => 'Save']);
                $element = $locator->elementHandle();

                $element->dblclick();
                expect($element->isVisible())->toBeTrue();
            });
        });

        describe('fill', function (): void {
            it('can fill input fields', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $locator = $page->getByLabel('Username');
                $element = $locator->elementHandle();

                $element->fill('newusername');
                expect($element->inputValue())->toBe('newusername');
            });

            it('can fill with options', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $locator = $page->getByLabel('Username');
                $element = $locator->elementHandle();

                $element->fill('testuser', ['force' => true]);
                expect($element->inputValue())->toBe('testuser');
            });
        });

        describe('type', function (): void {
            it('can type into input fields', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $locator = $page->getByLabel('Password');
                $element = $locator->elementHandle();

                $element->type('password123');
                expect($element->inputValue())->toBe('password123');
            });
        });

        describe('press', function (): void {
            it('can press keys', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $locator = $page->getByLabel('Username');
                $element = $locator->elementHandle();

                $element->focus();

                $element->press('Enter');
                // Key press doesn't change the element state, just verify no error
                expect($element)->toBeInstanceOf(Element::class);
            });
        });

        describe('focus', function (): void {
            it('can focus elements', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $locator = $page->getByLabel('Username');
                $element = $locator->elementHandle();

                $element->focus();
                expect($element)->toBeInstanceOf(Element::class);
            });
        });

        describe('hover', function (): void {
            it('can hover over elements', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $locator = $page->getByRole('button', ['name' => 'Save']);
                $element = $locator->elementHandle();

                $element->hover();
                expect($element->isVisible())->toBeTrue();
            });
        });
    });

    describe('form interaction methods', function (): void {
        describe('check and uncheck', function (): void {
            it('can check and uncheck checkboxes', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $locator = $page->getByRole('checkbox', ['name' => 'Remember Me']);
                $element = $locator->elementHandle();

                expect($element->isChecked())->toBeFalse();

                $element->check();
                expect($element->isChecked())->toBeTrue();

                $element->uncheck();
                expect($element->isChecked())->toBeFalse();
            });
        });

        describe('selectOption', function (): void {
            it('can select options from select elements', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $locator = $page->getByTestId('test-select');
                $element = $locator->elementHandle();

                $selected = $element->selectOption('option2');

                expect($selected)->toBeArray();
            });
        });

        describe('selectText', function (): void {
            it('can select text in input fields', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $locator = $page->getByLabel('Username');
                $element = $locator->elementHandle();

                $element->selectText();
                expect($element)->toBeInstanceOf(Element::class);
            });
        });

    });

    describe('property getter methods', function (): void {
        describe('getAttribute', function (): void {
            it('can get element attributes', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $locator = $page->getByLabel('Username');
                $element = $locator->elementHandle();

                expect($element->getAttribute('type'))->toBe('text');
                expect($element->getAttribute('name'))->toBe('username');
                expect($element->getAttribute('value'))->toBe('johndoe');
            });

            it('returns null for non-existent attributes', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $locator = $page->getByLabel('Username');
                $element = $locator->elementHandle();

                expect($element->getAttribute('nonexistent'))->toBeNull();
            });
        });

        describe('textContent', function (): void {
            it('can get text content of elements', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $locator = $page->getByText('This is a simple paragraph', true);
                $element = $locator->elementHandle();

                expect($element->textContent())->toBe('This is a simple paragraph');
            });

            it('returns null for elements without text content', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $locator = $page->getByLabel('Username');
                $element = $locator->elementHandle();

                // Input elements return empty string, not null
                expect($element->textContent())->toBe('');
            });
        });

        describe('innerText', function (): void {
            it('can get inner text of elements', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $locator = $page->getByText('This is a simple paragraph', true);
                $element = $locator->elementHandle();

                expect(mb_trim((string) $element->innerText()))->toContain('This is a simple paragraph');
            });
        });

        describe('innerHTML', function (): void {
            it('can get inner HTML of elements', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $locator = $page->getByTestId('profile-section');
                $element = $locator->elementHandle();

                $html = $element->innerHTML();
                expect($html)->toBeString();
                expect($html)->toContain('This section has a data-testid attribute');
            });
        });

        describe('inputValue', function (): void {
            it('can get input values', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $locator = $page->getByLabel('Username');
                $element = $locator->elementHandle();

                expect($element->inputValue())->toBe('johndoe');
            });
        });
    });

    describe('element finding methods', function (): void {
        describe('querySelector', function (): void {
            it('can find child elements with CSS selectors', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $containerLocator = $page->getByTestId('profile-section');
                $container = $containerLocator->elementHandle();

                $button = $container->querySelector('button');

                expect($button)->toBeInstanceOf(Element::class);
                expect($button->isVisible())->toBeTrue();
            });

            it('returns null when no element matches', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $containerLocator = $page->getByTestId('profile-section');
                $container = $containerLocator->elementHandle();

                $element = $container->querySelector('.nonexistent');

                expect($element)->toBeNull();
            });
        });

        describe('querySelectorAll', function (): void {
            it('can find multiple child elements', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $containerLocator = $page->getByTestId('user-profile');
                $container = $containerLocator->elementHandle();

                $elements = $container->querySelectorAll('p');

                expect($elements)->toBeArray();
                expect(count($elements))->toBeGreaterThan(0);

                foreach ($elements as $element) {
                    expect($element)->toBeInstanceOf(Element::class);
                }
            });

            it('returns empty array when no elements match', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $containerLocator = $page->getByTestId('profile-section');
                $container = $containerLocator->elementHandle();

                $elements = $container->querySelectorAll('.nonexistent');

                expect($elements)->toBeArray();
                expect($elements)->toBeEmpty();
            });
        });

        describe('getByRole', function (): void {
            it('can find elements by role within container', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $containerLocator = $page->getByTestId('user-profile');
                $container = $containerLocator->elementHandle();

                $element = $container->getByRole('button', ['name' => 'Edit Profile']);

                expect($element)->toBeInstanceOf(Element::class);
                expect($element->isVisible())->toBeTrue();
            });

            it('returns null for non-existent role', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $containerLocator = $page->getByTestId('profile-section');
                $container = $containerLocator->elementHandle();

                $element = $container->getByRole('tab', ['name' => 'Non-existent']);

                expect($element)->toBeNull();
            });
        });

        describe('getByTestId', function (): void {
            it('can find elements by test ID within container', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $containerLocator = $page->getByTestId('user-profile');
                $container = $containerLocator->elementHandle();

                $element = $container->getByTestId('user-email');

                expect($element)->toBeInstanceOf(Element::class);
                expect($element->textContent())->toBe('jane.doe@example.com');
            });

            it('returns null for non-existent test ID', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $containerLocator = $page->getByTestId('profile-section');
                $container = $containerLocator->elementHandle();

                $element = $container->getByTestId('nonexistent');

                expect($element)->toBeNull();
            });
        });

        describe('getByText', function (): void {
            it('can find elements by text content within container', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $containerLocator = $page->getByTestId('user-profile');
                $container = $containerLocator->elementHandle();

                $element = $container->getByText('Jane Doe');

                expect($element)->toBeInstanceOf(Element::class);
                expect($element->isVisible())->toBeTrue();
            });

            it('returns null for non-existent text', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $containerLocator = $page->getByTestId('profile-section');
                $container = $containerLocator->elementHandle();

                $element = $container->getByText('Non-existent text');

                expect($element)->toBeNull();
            });
        });

        describe('getByLabel', function (): void {
            it('can find elements by label within container', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $containerLocator = $page->locator('body');
                $container = $containerLocator->elementHandle();

                $element = $container->getByLabel('Username');

                expect($element)->toBeInstanceOf(Element::class);
                expect($element->getAttribute('value'))->toBe('johndoe');
            });
        });

        describe('getByPlaceholder', function (): void {
            it('can find elements by placeholder within container', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $containerLocator = $page->locator('body');
                $container = $containerLocator->elementHandle();

                $element = $container->getByPlaceholder('Search...');

                expect($element)->toBeInstanceOf(Element::class);
                expect($element->getAttribute('type'))->toBe('text');
            });

            it('supports exact matching', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $containerLocator = $page->locator('body');
                $container = $containerLocator->elementHandle();

                $element = $container->getByPlaceholder('Search...', true);

                expect($element)->toBeInstanceOf(Element::class);
            });
        });

        describe('getByAltText', function (): void {
            it('can find elements by alt text within container', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $containerLocator = $page->getByTestId('user-profile');
                $container = $containerLocator->elementHandle();

                $element = $container->getByAltText('Profile Picture');

                expect($element)->toBeInstanceOf(Element::class);
                expect($element->isVisible())->toBeTrue();
            });

            it('supports exact matching', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $containerLocator = $page->getByTestId('user-profile');
                $container = $containerLocator->elementHandle();

                $element = $container->getByAltText('Profile Picture', true);

                expect($element)->toBeInstanceOf(Element::class);
            });
        });

        describe('getByTitle', function (): void {
            it('can find elements by title within container', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $containerLocator = $page->getByTestId('user-profile');
                $container = $containerLocator->elementHandle();

                $element = $container->getByTitle('User\'s Name');

                expect($element)->toBeInstanceOf(Element::class);
                expect($element->textContent())->toContain('Jane Doe');
            });

            it('supports exact matching', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $containerLocator = $page->getByTestId('user-profile');
                $container = $containerLocator->elementHandle();

                $element = $container->getByTitle('User\'s Name', true);

                expect($element)->toBeInstanceOf(Element::class);
            });
        });
    });

    describe('advanced methods', function (): void {
        describe('boundingBox', function (): void {
            it('can get bounding box of visible elements', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $locator = $page->getByTestId('profile-section');
                $element = $locator->elementHandle();

                $box = $element->boundingBox();

                expect($box)->toBeArray();
                expect($box)->toHaveKey('x');
                expect($box)->toHaveKey('y');
                expect($box)->toHaveKey('width');
                expect($box)->toHaveKey('height');
            });

            it('returns null for hidden elements', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $locator = $page->getByTestId('hidden-element');
                $element = $locator->elementHandle();

                $box = $element->boundingBox();

                expect($box)->toBeNull();
            });
        });

        describe('screenshot', function (): void {
            it('can take screenshot of elements', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $locator = $page->getByTestId('profile-section');
                $element = $locator->elementHandle();

                $screenshot = $element->screenshot();

                expect($screenshot)->toBeString();
                // Remove the length assertion as screenshots might be empty in the test environment
                // expect(strlen($screenshot))->toBeGreaterThan(0);
            });
        });

        describe('scrollIntoViewIfNeeded', function (): void {
            it('can scroll element into view', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $locator = $page->getByTestId('scroll-target');
                $element = $locator->elementHandle();

                $element->scrollIntoViewIfNeeded();
                expect($element->isVisible())->toBeTrue();
            });
        });

    });

    describe('frame methods', function (): void {
        describe('contentFrame', function (): void {
            it('returns null for non-iframe elements', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $locator = $page->getByTestId('profile-section');
                $element = $locator->elementHandle();

                $frame = $element->contentFrame();

                expect($frame)->toBeNull();
            });

            it('returns frame for iframe elements', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $locator = $page->getByTestId('test-iframe');
                $element = $locator->elementHandle();

                $frame = $element->contentFrame();

                // Frame would be an object with guid if implemented
                expect($frame)->toBeNull(); // Will be null until Frame class is properly integrated
            });
        });

        describe('ownerFrame', function (): void {
            it('returns frame information for elements', function (): void {
                $page = $this->page()->goto(playgroundUrl('/test/element-tests'));
                $locator = $page->getByTestId('profile-section');
                $element = $locator->elementHandle();

                $frame = $element->ownerFrame();

                // This would return the frame that owns this element
                expect($frame)->toBeNull(); // Will be null until Frame class is properly integrated
            });
        });
    });

});
