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
| `--url=` | `APP_URL` from `.env` | Base URL to open |
| `--visit=` | `/` | Path to open on start (e.g. `/dashboard`) |
| `--test-id-attribute=` | `id` | HTML attribute used for element selectors |
| `--device=` | — | Emulate a device (e.g. `"iPhone 15"`) |
| `--viewport=` | — | Viewport size in pixels (e.g. `1280,800`) |
| `--acting-as=` | — | Name of a saved auth state (see below) |
| `--server` | — | Start `php artisan serve` before recording |
| `--env=` | `local` | Environment passed to `--server` and `--migrate-fresh` |
| `--migrate-fresh` | — | Run `migrate:fresh` before opening the browser |
| `--seed` | — | Seed the database after `--migrate-fresh` |

**Example:**

```bash
vendor/bin/pest --record --visit=/dashboard --acting-as=user
```

**Example output:**

```php
it('can switch to dark mode', function (): void {
    $this->actingAs(\App\Models\User::factory()->create());

    visit('/dashboard')
        ->click('Settings')
        ->click('Appearance')
        ->click('Dark');
});
```

**Why you need `--server`:**

`pest --record` opens a real browser (Playwright codegen) against a running HTTP server. The recorder connects to your app just like a normal user would — it does NOT use the in-process test server that runs during `vendor/bin/pest`.

If your app is not already running, pass `--server` to start `php artisan serve` automatically:

```bash
vendor/bin/pest --record --server --env=local --visit=/dashboard
```

Without `--server`, you must start the dev server yourself before recording:

```bash
php artisan serve &
vendor/bin/pest --record
```

**Auth state and the environment mismatch problem (`--acting-as`):**

Tests run with a fresh, isolated database (`APP_ENV=testing`). If you record a login sequence without `--acting-as`, your real credentials from the local environment are captured — but those credentials don't exist in the test database.

`--acting-as` solves this in two steps:

1. **First use:** a browser opens at `/login`. Log in with your real credentials. The browser session (cookies, localStorage) is saved to a temp file.
2. **Subsequent recordings:** the saved session is loaded automatically, so the browser starts already authenticated. No login form is filled, so no credentials are captured.

The generated test replaces the login flow with `actingAs(User::factory()->create())`, which creates a real user in the test database and authenticates without touching the login form:

```bash
# First run saves the auth session; subsequent runs reuse it
vendor/bin/pest --record --server --acting-as=user --visit=/dashboard
```

If you record without `--acting-as` and Pest detects a password field in the recording, it automatically strips the login sequence and injects `actingAs()` instead.

**Fresh database before recording:**

```bash
vendor/bin/pest --record --server --migrate-fresh --seed --env=local --visit=/dashboard
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
