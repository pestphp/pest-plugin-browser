# Pest Plugin Browser

This repository contains the Pest Plugin Browser.
> If you want to start testing your application with Pest, visit the main **[Pest Repository](https://github.com/pestphp/pest)**.

This plugin provides a browser testing API for [Pest](https://pestphp.com) that is similar to Laravel Dusk, but uses [Playwright](https://playwright.dev/) under the hood.

## Why Pest Browser Kit (Playwright)?

Traditional browser testing frameworks like Laravel Dusk often rely on older engines (e.g., Facebook WebDriver).  This project aims to provide the familiar and convenient Dusk-like API while leveraging the power and modern features of Playwright.  Here's why that matters:

- **Modern Engine:** Playwright is a modern browser automation library built for speed, reliability, and cross-browser compatibility.
- **Parallel Testing:**  A key advantage of Playwright is its ability to run tests in parallel, drastically reducing the overall test execution time.  This is a significant improvement over older frameworks.
- **Cross-Browser Support:** Playwright supports Chromium, Firefox, and WebKit, allowing you to test your application across multiple browsers with a single API.
- **Auto-Waiting:** Playwright automatically waits for elements to be actionable before interacting with them, reducing flakiness and simplifying test code.
- **Debugging Tools:** Playwright offers excellent debugging tools, including tracing, screenshots, and video recording.
- **Familiar API:** If you're used to the expressive syntax, you'll feel at home, minimizing the learning curve.

In short, this plugin gives you the best of both worlds: a familiar API and the performance and features of a modern browser automation engine.


## Community & Resources

- Explore our docs at **[pestphp.com »](https://pestphp.com)**
- Follow us on Twitter at **[@pestphp »](https://twitter.com/pestphp)**
- Join us at **[discord.gg/kaHY6p54JH »](https://discord.gg/kaHY6p54JH)** or **[t.me/+kYH5G4d5MV83ODk0 »](https://t.me/+kYH5G4d5MV83ODk0)**

## Installation

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

## License

Pest is an open-sourced software licensed under the **[MIT license](https://opensource.org/licenses/MIT)**.
