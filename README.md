# Pest Plugin Browser

This repository contains the Pest Plugin Browser.
> If you want to start testing your application with Pest, visit the main **[Pest Repository](https://github.com/pestphp/pest)**.

## Community & Resources

- Explore our docs at **[pestphp.com »](https://pestphp.com)**
- Follow us on Twitter at **[@pestphp »](https://twitter.com/pestphp)**
- Join us at **[discord.gg/kaHY6p54JH »](https://discord.gg/kaHY6p54JH)** or **[t.me/+kYH5G4d5MV83ODk0 »](https://t.me/+kYH5G4d5MV83ODk0)**

## Installation (for development purposes)

1. Install PHP dependencies using Composer:
```bash
composer install
```

2. Install Node.js dependencies:
```bash
npm install
```

3. Install Playwright browsers:
```bash
npx playwright install
```

## Running Tests

To run the test suite, execute:
```bash
./vendor/bin/pest
```

## The Playground

![Playground_interacting-with-elements.png](docs/Playground_interacting-with-elements.png)

For each Operation/Assertion, we add a corresponding Test.

We can make use of the `playgroundUrl()` helper, to get its URL during the test.

We can provide a URI that will be appended, e.g: `playgroundUrl('/test/interactive-elements')`.

### Routes and views for testing

Check the `playground/resources/views/test-pages` folder for existing views.

They are accessible by the playground route `/test/{page}`.

E.g.: The view `resources/views/test-pages/interactive-elements.blade.php` is visited on `playgroundUrl('/test/interactive-elements')`. 

The playground is standard Laravel App, where you may add a page with a feature for your test.

Just add the view, and the Nav Menu will automatically update based on the view name.

## License

Pest is an open-sourced software licensed under the **[MIT license](https://opensource.org/licenses/MIT)**.


# Documentation

Pest Plugin Browser brings end-to-end testing to the elegant syntax from Pest.
This allows to test your application in a browser environment, enabling to test all the components, such as frontend, backend and database.

## Installation

TBD

## Interacting with Elements

### Waiting for Elements

#### Waiting

Pause the test for the specified number of milliseconds.

```php
    $this->pause(5000); // Pause for 5 seconds
```

## Available Assertions

- [assertAttribute](#assertAttribute)
- [assertAttributeContains](#assertAttributeContains)
- [assertAttributeMissing](#assertAttributeMissing)
- [assertDontSee](#assertDontSee)
- [assertQueryStringHas](#assertQueryStringHas)
- [assertQueryStringMissing](#assertQueryStringMissing)
- [assertPresent](#assertpresent)
- [assertNotPresent](#assertnotpresent)
- [assertScript](#assertscript)
- [assertVisible](#assertvisible)
- [assertMissing](#assertmissing)

#### assertAttribute

Assert that the specified element has the expected attribute and value:

```php
$this->visit($url)
    ->assertAttribute('html', 'data-theme', 'light');
```

#### assertAttributeContains

Assert that the specified element has the expected attribute and the value contains a specific value:

```php
$this->visit($url)
    ->assertAttributeContains('html', 'data-theme', 'ight');
```

#### assertAttributeMissing

Assert that the specified element is missing a particular attribute :

```php
$this->visit($url)
    ->assertAttributeMissing('html', 'data-missing');
```

#### assertDontSee

Assert that the given text is not present on the page:

```php
$this->visit($url)
    ->assertDontSee('we are a streaming service');
```

#### assertVisible

Assert that an element with the given selector is visible:

```php
test('assert visible', function () {
    $url = 'https://laravel.com';

    $this->visit($url)
        ->assertVisible('h1:visible');
});
```

#### assertMissing

Assert that an element with the given selector is hidden:

```php
test('assert missing', function () {
    $url = 'https://laravel.com';

    $this->visit($url)
        ->assertMissing('a.hidden');
```

#### assertQueryStringHas

Assert that the given query string is present in the url:

```php
$this->visit($url)
    ->assertQueryStringHas('q', 'test');
```

#### assertQueryStringMissing

Assert that the given query string is not present in the url:

```php
$this->visit($url)
    ->assertQueryStringMissing('q', 'test-1');
```

#### assertScript

Assert that the given script returns the expected value:

```php
$this->visit($url)
    ->assertScript('document.querySelector("title").textContent.includes("Laravel")', true);
```

#### assertPresent

Assert that the element with a given selector is present on the page:

```php
$this->visit($url)
    ->assertPresent('h1:visible');
```

#### assertNotPresent

Assert that the element with a given selector is not present on the page:

```php
$this->visit($url)
    ->assertNotPresent('a.non-existing-class');
```
