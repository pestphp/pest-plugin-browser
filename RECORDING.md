# Recording Guide

## How it works

`vendor/bin/pest --record` automatically starts an HTTP server with `APP_ENV=testing` before the browser opens, then shuts it down when recording ends — matching the same behavior as running browser tests normally with `vendor/bin/pest`.

The browser opens at full screen resolution by default (auto-detected per OS). Close the browser when done. Pest prompts for a test description and output file, then writes the generated test to `tests/Browser/`.

## Options

| Option | Default | Description |
|---|---|---|
| `--url=` | auto | Skip auto-server; connect to this URL instead |
| `--visit=` | `/` | Path to open on start |
| `--test-id-attribute=` | `id` | HTML attribute used for element selectors |
| `--device=` | — | Emulate a device (e.g. `"iPhone 15"`) |
| `--viewport=` | screen resolution | Viewport size in pixels (e.g. `1280,800`) |
| `--acting-as=` | — | Name for the auth state (see below) |
| `--env=` | `testing` | Environment for the auto-started server |
| `--migrate-fresh` | — | Run `migrate:fresh` before opening the browser |
| `--seed` | — | Seed the database after `--migrate-fresh` |

## Recording authenticated flows (`--acting-as`)

Tests run with `APP_ENV=testing` (fresh, isolated DB). Recording against credentials that only exist in your local DB means those credentials won't exist when the test runs.

`--acting-as` solves this without manual login: before the browser opens, Pest bootstraps the Laravel app, creates a factory user, starts a session authenticated as that user, and injects a valid session cookie into the browser. The browser starts pre-authenticated.

```bash
vendor/bin/pest --record --acting-as=user --visit=/dashboard
```

The generated test uses `$this->actingAs(\App\Models\User::factory()->create())` — no real credentials, works in any environment.

## Fresh database before recording

```bash
vendor/bin/pest --record --migrate-fresh --seed --visit=/dashboard
```

Useful when you want to record against a predictable dataset.

## Recording against an existing server

Pass `--url=` to skip the auto-server entirely:

```bash
vendor/bin/pest --record --url=http://localhost:8000 --visit=/dashboard
```

Useful when you have Herd, Valet, or a running `php artisan serve` instance.
