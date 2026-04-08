## Pest Browser Testing

- Use `visit('/path')` to navigate to a page. Chain interactions and assertions fluently.
- You don't need absolute URLs in `visit()`. Just use the path (e.g. `visit('/dashboard')`) and Pest will resolve it.
- **Note:** Always provide a descriptive `filename:` to screenshots.

### Navigation & Screenshots

```php
$this->visit('/')->screenshot(filename: 'homepage');
$this->visit('/dashboard')->screenshot(filename: 'dashboard', fullPage: true);
$this->visit('/')->screenshotElement('.hero', filename: 'hero-section');
```

Visual regression testing to catch unintended UI changes:

```php
$this->visit('/')->assertScreenshotMatches();
$this->visit('/dashboard')->assertScreenshotMatches(fullPage: true);
```

### Responsiveness & Device Emulation

Test on mobile and specific devices:

```php
$this->visit('/')->on()->mobile()->screenshot(filename: 'homepage-mobile');
$this->visit('/')->on()->iPhone14Pro()->screenshot(filename: 'homepage-iphone14pro');
$this->visit('/')->on()->macbook14()->screenshot(filename: 'homepage-macbook14');
```

Custom viewport:

```php
$this->visit('/')->resize(375, 812)->screenshot(filename: 'homepage-375x812');
```

Dark mode:

```php
$this->visit('/')->inDarkMode()->screenshot(filename: 'homepage-dark');
```

### Interactions

Click, type, and submit forms:

```php
$this->visit('/')->click('Login')->assertPathIs('/login');

$this->visit('/login')
    ->type('email', 'user@example.com')
    ->type('password', 'secret')
    ->press('Sign in')
    ->assertPathIs('/dashboard');
```

Slow typing for fields with debounce or live validation:

```php
$this->visit('/search')->typeSlowly('query', 'pest php')->assertSee('Results');
```

Dropdowns, checkboxes, and radio buttons:

```php
$this->visit('/settings')
    ->select('timezone', 'America/New_York')
    ->check('notifications')
    ->uncheck('marketing')
    ->radio('plan', 'pro')
    ->press('Save')
    ->assertSee('Settings saved');
```

Clear and append to fields:

```php
$this->visit('/form')->clear('name')->type('name', 'New Name');
$this->visit('/form')->append('tags', ', new-tag');
```

File uploads:

```php
$this->visit('/upload')->attach('avatar', '/path/to/photo.jpg')->press('Upload');
```

Hover, drag and drop, and keyboard input:

```php
$this->visit('/')->hover('.dropdown-trigger')->assertSee('Menu Item');
$this->visit('/board')->drag('#task-1', '#column-done');
$this->visit('/editor')->keys('.editor', 'Hello World');
```

Hold modifier keys during interactions:

```php
$this->visit('/editor')->withKeyDown('Shift', function ($page) {
    $page->click('#item-1')->click('#item-5');
});
```

Interact within iframes:

```php
$this->visit('/embed')->withinIframe('#payment-frame', function ($iframe) {
    $iframe->type('card-number', '4242424242424242')->press('Pay');
});
```

Press and wait for async operations:

```php
$this->visit('/form')->pressAndWaitFor('Submit', 2)->assertSee('Submitted');
```

### Content Assertions

```php
$this->visit('/')->assertSee('Welcome');
$this->visit('/')->assertDontSee('Error');
$this->visit('/')->assertSeeIn('.alert', 'Success');
$this->visit('/')->assertDontSeeIn('.alert', 'Warning');
$this->visit('/')->assertCount('.product-card', 5);
$this->visit('/')->assertSeeLink('Documentation');
$this->visit('/')->assertDontSeeLink('Admin');
$this->visit('/')->assertTitle('Home — My App');
$this->visit('/')->assertTitleContains('Home');
$this->visit('/')->assertSourceHas('<meta name="description"');
$this->visit('/')->assertSourceMissing('<div class="debug"');
```

### Element State Assertions

```php
$this->visit('/')->assertVisible('.navbar');
$this->visit('/')->assertMissing('.loading-spinner');
$this->visit('/')->assertPresent('input[name=email]');
$this->visit('/')->assertNotPresent('.modal');
$this->visit('/form')->assertEnabled('submit');
$this->visit('/form')->assertDisabled('delete');
$this->visit('/form')->assertButtonEnabled('Save');
$this->visit('/form')->assertButtonDisabled('Delete');
```

### Form Assertions

```php
$this->visit('/settings')->assertValue('name', 'John Doe');
$this->visit('/settings')->assertValueIsNot('name', '');
$this->visit('/settings')->assertChecked('notifications');
$this->visit('/settings')->assertNotChecked('marketing');
$this->visit('/settings')->assertIndeterminate('select-all');
$this->visit('/form')->assertRadioSelected('plan', 'pro');
$this->visit('/form')->assertRadioNotSelected('plan', 'free');
$this->visit('/form')->assertSelected('country', 'US');
$this->visit('/form')->assertNotSelected('country', 'UK');
```

### URL Assertions

```php
$this->visit('/dashboard')->assertUrlIs('http://localhost/dashboard');
$this->visit('/dashboard')->assertPathIs('/dashboard');
$this->visit('/dashboard')->assertPathIsNot('/login');
$this->visit('/docs/install')->assertPathBeginsWith('/docs');
$this->visit('/docs/install')->assertPathEndsWith('/install');
$this->visit('/docs/install')->assertPathContains('docs');
$this->visit('/dashboard')->assertSchemeIs('http');
$this->visit('/dashboard')->assertHostIs('localhost');
$this->visit('/search?q=pest')->assertQueryStringHas('q');
$this->visit('/search')->assertQueryStringMissing('q');
$this->visit('/page#section')->assertFragmentIs('section');
$this->visit('/page#section-one')->assertFragmentBeginsWith('section');
```

### Attribute Assertions

```php
$this->visit('/')->assertAttribute('.logo', 'alt', 'My App');
$this->visit('/')->assertAttributeMissing('.input', 'disabled');
$this->visit('/')->assertAttributeContains('.btn', 'class', 'primary');
$this->visit('/')->assertAttributeDoesntContain('.btn', 'class', 'hidden');
$this->visit('/')->assertDataAttribute('.card', 'id', '42');
$this->visit('/')->assertAriaAttribute('.menu', 'expanded', 'true');
```

### Quality & Accessibility

```php
$this->visit('/')->assertNoJavaScriptErrors();
$this->visit('/')->assertNoConsoleLogs();
$this->visit('/')->assertNoSmoke();
$this->visit('/')->assertNoAccessibilityIssues();
$this->visit('/')->assertScript('document.title', 'My App');
```

### Data Retrieval

```php
$text = $this->visit('/')->text('.heading');
$href = $this->visit('/')->attribute('.link', 'href');
$value = $this->visit('/form')->value('email');
$html = $this->visit('/')->content();
$url = $this->visit('/redirect')->url();
$count = $this->visit('/')->script('document.querySelectorAll(".item").length');
```

### Waiting

```php
$this->visit('/')->wait(2); // Wait 2 seconds
$this->visit('/form')->pressAndWaitFor('Submit', 2); // Press and wait
```

Configure default timeout in `Pest.php`:

```php
pest()->browser()->timeout(10000); // 10 seconds
```

### Multiple Pages

Test multiple pages simultaneously:

```php
[$home, $about] = visit(['/', '/about']);
$home->assertSee('Welcome');
$about->assertSee('About Us');
```

### Configuration

Set browser in `Pest.php`:

```php
pest()->browser()->inFirefox();
pest()->browser()->inWebkit();
```

Override via CLI: `./vendor/bin/pest --browser firefox`.

Configure locale, timezone, and user agent:

```php
$this->visit('/')->withLocale('fr-FR')->assertSee('Bienvenue');
$this->visit('/')->withTimezone('America/New_York');
$this->visit('/')->withUserAgent('Googlebot');
$this->visit('/')->withHost('subdomain.localhost');
```

Geolocation:

```php
$this->visit('/nearby')->geolocation(40.7128, -74.0060)->assertSee('New York');
```

### Debugging

- `debug()` — pauses execution and opens the browser, focusing on the current test.
- `tinker()` — opens an interactive PHP session within the page context.
- `headed()` — runs the test with a visible browser window.
- `waitForKey()` — opens the browser and waits for a key press before continuing.
- `--debug` CLI flag — opens the browser and pauses on test failure.
- `--headed` CLI flag — runs all tests with visible browser windows.

### Combining Browser and Backend Assertions

- **Important:** Always assert a frontend change first (e.g. `assertSee`, `assertPathIs`) to confirm the action completed before checking backend side effects.

```php
Mail::fake();
$this->visit('/contact')
    ->type('email', 'test@example.com')
    ->type('message', 'Hello')
    ->press('Send')
    ->assertSee('Message sent');
Mail::assertSent(ContactForm::class);
```

```php
Notification::fake();
$this->visit('/register')
    ->type('name', 'John')
    ->type('email', 'john@example.com')
    ->type('password', 'password')
    ->press('Register')
    ->assertPathIs('/dashboard');
Notification::assertSentTo(User::first(), WelcomeNotification::class);
```

```php
$this->visit('/checkout')
    ->type('card', '4242424242424242')
    ->press('Pay')
    ->assertSee('Transaction processed');
expect(Order::count())->toBe(1);
```

### Running in Parallel

Run browser tests in parallel for faster execution:

```
./vendor/bin/pest --parallel
```
