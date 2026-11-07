This repository contains the Pest Plugin for Browser.

> If you want to start testing your application with Pest, visit the main **[Pest Repository](https://github.com/pestphp/pest)**.

- Explore our docs at **[pestphp.com »](https://pestphp.com)**
- Follow the creator Nuno Maduro:
    - YouTube: **[youtube.com/@nunomaduro](https://www.youtube.com/@nunomaduro)** — Videos every weekday
    - Twitch: **[twitch.tv/enunomaduro](https://www.twitch.tv/enunomaduro)** — Streams (almost) every weekday
    - Twitter / X: **[x.com/enunomaduro](https://x.com/enunomaduro)**
    - LinkedIn: **[linkedin.com/in/nunomaduro](https://www.linkedin.com/in/nunomaduro)**
    - Instagram: **[instagram.com/enunomaduro](https://www.instagram.com/enunomaduro)**
    - Tiktok: **[tiktok.com/@enunomaduro](https://www.tiktok.com/@enunomaduro)**

## Remote Playwright Servers

By default, the plugin starts a local Playwright server using your project's npm installation. To use an existing remote server instead, configure its WebSocket endpoint in `tests/Pest.php`, before running browser tests:

```php
pest()->browser()->useRemoteConnection('ws://playwright.example.com:3000');
```

For a single test run, pass the endpoint to Pest with `--ws`:

```sh
vendor/bin/pest --ws='ws://playwright.example.com:3000'
```

The endpoint can also be supplied as a separate argument with `--ws ws://playwright.example.com:3000`.

Secure `wss://` endpoints, paths, and query parameters are supported. The plugin does not require a local Playwright installation when using a remote server, and does not start or stop the remote process. This configuration also works with parallel tests.

Run a compatible Playwright server (currently version 1.63.0 or newer), for example:

```sh
npx playwright run-server --host 0.0.0.0 --port 3000 --mode launchServer
```

The remote server must have the required browsers installed, and its browsers must be able to reach the application being tested. The application's HTTP server behavior is unchanged: it binds to `127.0.0.1` by default. For remote machines or containers, provide a reachable application URL and, when needed, a custom HTTP server via `pest()->browser()->httpServer(...)`. `withHost(...)` controls the application host, not the remote Playwright endpoint. Keep remote endpoints on a trusted network or behind authenticated access.

Pest is an open-sourced software licensed under the **[MIT license](https://opensource.org/licenses/MIT)**.
