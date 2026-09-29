#!/usr/bin/env node
'use strict';

// Starts a Playwright server backed by a browser that is already running and
// speaks the Chrome DevTools Protocol (a remote Chrome, Obscura, Browserless...)
// instead of a locally launched one. The plugin talks to it exactly like it
// talks to `playwright run-server --mode launchServer`.
//
// Usage: node cdp-server.js --endpoint ws://127.0.0.1:9222 --host 127.0.0.1 --port 8000

const path = require('path');

function argument(name, fallback) {
    const index = process.argv.indexOf(`--${name}`);

    return index === -1 || index + 1 >= process.argv.length ? fallback : process.argv[index + 1];
}

const endpointURL = argument('endpoint');
const host = argument('host', '127.0.0.1');
const port = Number(argument('port', '0'));

if (!endpointURL) {
    console.error('Missing --endpoint argument, e.g. --endpoint ws://127.0.0.1:9222');
    process.exit(1);
}

const bundle = require(require.resolve('playwright-core/lib/coreBundle', {
    paths: [process.cwd(), path.join(process.cwd(), 'node_modules', 'playwright')],
}));

(async () => {
    const playwright = bundle.server.createPlaywright({ sdkLanguage: 'javascript', isServer: true });

    const browser = await playwright.chromium.connectOverCDP(bundle.server.nullProgress, {
        endpointURL,
        timeout: 30000,
    });

    const server = new bundle.remote.PlaywrightServer({
        mode: 'launchServer',
        path: '/',
        maxConnections: Infinity,
        preLaunchedBrowser: browser,
    });

    const wsEndpoint = await server.listen(port, host);

    browser.on('disconnected', () => {
        console.error(`Browser at ${endpointURL} disconnected.`);
        process.exit(1);
    });

    console.log(`Listening on ${wsEndpoint}`);
})().catch((error) => {
    console.error(error.message);
    process.exit(1);
});
