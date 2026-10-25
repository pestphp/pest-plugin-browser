## Pest Browser Testing

- Use `visit('/path')` to navigate to a page. Chain interactions and assertions fluently.
- You don't need absolute URLs in `visit()`. Just use the path (e.g. `visit('/dashboard')`) and Pest will resolve it.
- Each `visit()` is a separate global function call — you cannot chain `->visit()` from a page object. Use a semicolon to separate multiple page navigations: `visit('/login')->press('Log in'); visit('/dashboard')->assertSee('Welcome');`
- Browser sessions persist across `visit()` calls within the same test — after logging in, subsequent `visit()` calls remain authenticated.
- Use `$this->actingAs($user)` before `visit()` to authenticate without filling the login form. This is the preferred approach for testing authenticated pages.
- `assertCount('selector', n)` matches all elements on the entire page, including sidebar, footer, and navigation. Use a specific CSS selector to target only the elements you care about.
- **Note:** Always provide a descriptive `filename:` to screenshots.

### Navigation & Screenshots

```php
visit('/')->screenshot(filename: 'homepage');
visit('/dashboard')->screenshot(filename: 'dashboard', fullPage: true);
visit('/')->screenshotElement('.hero', filename: 'hero-section');
```

Visual regression testing to catch unintended UI changes:

```php
visit('/')->assertScreenshotMatches();
visit('/dashboard')->assertScreenshotMatches(fullPage: true);
```

### Responsiveness & Device Emulation

Test on mobile and specific devices:

```php
visit('/')->on()->mobile()->screenshot(filename: 'homepage-mobile');
visit('/')->on()->iPhone14Pro()->screenshot(filename: 'homepage-iphone14pro');
visit('/')->on()->macbook14()->screenshot(filename: 'homepage-macbook14');
```

Custom viewport:

```php
visit('/')->resize(375, 812)->screenshot(filename: 'homepage-375x812');
```

Dark mode:

```php
visit('/')->inDarkMode()->screenshot(filename: 'homepage-dark');
```

### Interactions

Click, type, and submit forms:

```php
visit('/')->click('Login')->assertPathIs('/login');

visit('/login')
    ->type('email', 'user@example.com')
    ->type('password', 'secret')
    ->press('Sign in')
    ->assertPathIs('/dashboard');
```

Slow typing for fields with debounce or live validation:

```php
visit('/search')->typeSlowly('query', 'pest php')->assertSee('Results');
```

Dropdowns, checkboxes, and radio buttons:

```php
visit('/settings')
    ->select('timezone', 'America/New_York')
    ->check('notifications')
    ->uncheck('marketing')
    ->radio('plan', 'pro')
    ->press('Save')
    ->assertSee('Settings saved');
```

Clear and append to fields:

```php
visit('/form')->clear('name')->type('name', 'New Name');
visit('/form')->append('tags', ', new-tag');
```

File uploads:

```php
visit('/upload')->attach('avatar', '/path/to/photo.jpg')->press('Upload');
```

Hover, drag and drop, and keyboard input:

```php
visit('/')->hover('.dropdown-trigger')->assertSee('Menu Item');
visit('/board')->drag('#task-1', '#column-done');
visit('/editor')->keys('.editor', 'Hello World');
```

Hold modifier keys during interactions:

```php
visit('/editor')->withKeyDown('Shift', function ($page) {
    $page->click('#item-1')->click('#item-5');
});
```

Interact within iframes:

```php
visit('/embed')->withinIframe('#payment-frame', function ($iframe) {
    $iframe->type('card-number', '4242424242424242')->press('Pay');
});
```

Press and wait for async operations:

```php
visit('/form')->pressAndWaitFor('Submit', 2)->assertSee('Submitted');
```

### Content Assertions

```php
visit('/')->assertSee('Welcome');
visit('/')->assertDontSee('Error');
visit('/')->assertSeeIn('.alert', 'Success');
visit('/')->assertDontSeeIn('.alert', 'Warning');
visit('/')->assertCount('.product-card', 5);
visit('/')->assertSeeLink('Documentation');
visit('/')->assertDontSeeLink('Admin');
visit('/')->assertTitle('Home — My App');
visit('/')->assertTitleContains('Home');
visit('/')->assertSourceHas('<meta name="description"');
visit('/')->assertSourceMissing('<div class="debug"');
```

### Element State Assertions

```php
visit('/')->assertVisible('.navbar');
visit('/')->assertMissing('.loading-spinner');
visit('/')->assertPresent('input[name=email]');
visit('/')->assertNotPresent('.modal');
visit('/form')->assertEnabled('submit');
visit('/form')->assertDisabled('delete');
visit('/form')->assertButtonEnabled('Save');
visit('/form')->assertButtonDisabled('Delete');
```

### Form Assertions

```php
visit('/settings')->assertValue('name', 'John Doe');
visit('/settings')->assertValueIsNot('name', '');
visit('/settings')->assertChecked('notifications');
visit('/settings')->assertNotChecked('marketing');
visit('/settings')->assertIndeterminate('select-all');
visit('/form')->assertRadioSelected('plan', 'pro');
visit('/form')->assertRadioNotSelected('plan', 'free');
visit('/form')->assertSelected('country', 'US');
visit('/form')->assertNotSelected('country', 'UK');
```

### URL Assertions

```php
visit('/dashboard')->assertUrlIs('http://localhost/dashboard');
visit('/dashboard')->assertPathIs('/dashboard');
visit('/dashboard')->assertPathIsNot('/login');
visit('/docs/install')->assertPathBeginsWith('/docs');
visit('/docs/install')->assertPathEndsWith('/install');
visit('/docs/install')->assertPathContains('docs');
visit('/dashboard')->assertSchemeIs('http');
visit('/dashboard')->assertHostIs('localhost');
visit('/search?q=pest')->assertQueryStringHas('q');
visit('/search')->assertQueryStringMissing('q');
visit('/page#section')->assertFragmentIs('section');
visit('/page#section-one')->assertFragmentBeginsWith('section');
```

### Attribute Assertions

```php
visit('/')->assertAttribute('.logo', 'alt', 'My App');
visit('/')->assertAttributeMissing('.input', 'disabled');
visit('/')->assertAttributeContains('.btn', 'class', 'primary');
visit('/')->assertAttributeDoesntContain('.btn', 'class', 'hidden');
visit('/')->assertDataAttribute('.card', 'id', '42');
visit('/')->assertAriaAttribute('.menu', 'expanded', 'true');
```

### Quality & Accessibility

```php
visit('/')->assertNoJavaScriptErrors();
visit('/')->assertNoConsoleLogs();
visit('/')->assertNoSmoke();
visit('/')->assertNoAccessibilityIssues();
visit('/')->assertScript('document.title', 'My App');
```

### Data Retrieval

```php
$text = visit('/')->text('.heading');
$href = visit('/')->attribute('.link', 'href');
$value = visit('/form')->value('email');
$html = visit('/')->content();
$url = visit('/redirect')->url();
$count = visit('/')->script('document.querySelectorAll(".item").length');
```

### Waiting

```php
visit('/')->wait(2); // Wait 2 seconds
visit('/form')->pressAndWaitFor('Submit', 2); // Press and wait
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
visit('/')->withLocale('fr-FR')->assertSee('Bienvenue');
visit('/')->withTimezone('America/New_York');
visit('/')->withUserAgent('Googlebot');
visit('/')->withHost('subdomain.localhost');
```

Geolocation:

```php
visit('/nearby')->geolocation(40.7128, -74.0060)->assertSee('New York');
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
visit('/contact')
    ->type('email', 'test@example.com')
    ->type('message', 'Hello')
    ->press('Send')
    ->assertSee('Message sent');
Mail::assertSent(ContactForm::class);
```

```php
Notification::fake();
visit('/register')
    ->type('name', 'John')
    ->type('email', 'john@example.com')
    ->type('password', 'password')
    ->press('Register')
    ->assertPathIs('/dashboard');
Notification::assertSentTo(User::first(), WelcomeNotification::class);
```

```php
visit('/checkout')
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
