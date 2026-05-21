This repository contains the Pest Plugin for Browser.

> If you want to start testing your application with Pest, visit the main **[Pest Repository](https://github.com/pestphp/pest)**.

## Recording Tests

Generate a browser test by recording your interactions live in the browser — no test code to write by hand.

```bash
vendor/bin/pest --record
```

Playwright's codegen opens a browser window. Interact with your application, then close the browser. Pest prompts for a test description and output file, then writes the generated test to `tests/Browser/`.

**Options:**

| Option | Default | Description |
|---|---|---|
| `--url=` | `http://localhost:8000` | Base URL to open |
| `--visit=` | `/` | Path to open on start (e.g. `/login`) |
| `--test-id-attribute=` | `data-test` | HTML attribute used for element selectors |
| `--device=` | — | Emulate a device (e.g. `"iPhone 15"`) |
| `--viewport=` | — | Viewport size in pixels (e.g. `1280,800`) |
| `--acting-as=` | — | Name of a saved browser auth state to load |

**Example:**

```bash
vendor/bin/pest --record --url=https://example.com --visit=/login --test-id-attribute=id
```

**Example output:**

```php
it('can login', function (): void {
    $page = visit('/login');
    $page->fill('#email', 'user@example.com');
    $page->fill('#password', 'secret');
    $page->click('Sign in');
    $page->assertSee('Dashboard');
});
```

**Auth state (`--acting-as`):**

Saves and reloads browser-level auth state (cookies, localStorage) between recordings. On first use, a login sequence is recorded and saved. Subsequent recordings load the saved state automatically.

```bash
vendor/bin/pest --record --acting-as=admin
```

**Requirements:** `npm install -D @playwright/test` and `npx playwright install`.

- Explore our docs at **[pestphp.com »](https://pestphp.com)**
- Follow the creator Nuno Maduro:
    - YouTube: **[youtube.com/@nunomaduro](https://www.youtube.com/@nunomaduro)** — Videos every weekday
    - Twitch: **[twitch.tv/enunomaduro](https://www.twitch.tv/enunomaduro)** — Streams (almost) every weekday
    - Twitter / X: **[x.com/enunomaduro](https://x.com/enunomaduro)**
    - LinkedIn: **[linkedin.com/in/nunomaduro](https://www.linkedin.com/in/nunomaduro)**
    - Instagram: **[instagram.com/enunomaduro](https://www.instagram.com/enunomaduro)**
    - Tiktok: **[tiktok.com/@enunomaduro](https://www.tiktok.com/@enunomaduro)**

Pest is an open-sourced software licensed under the **[MIT license](https://opensource.org/licenses/MIT)**.
