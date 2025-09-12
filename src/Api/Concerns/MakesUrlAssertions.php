<?php

declare(strict_types=1);

namespace Pest\Browser\Api\Concerns;

use Pest\Browser\Api\Webpage;
use RuntimeException;

/**
 * @mixin Webpage
 */
trait MakesUrlAssertions
{
    /**
     * Assert that the current URL (without the query string) matches the given string.
     */
    public function assertUrlIs(string $url): Webpage
    {
        $pattern = str_replace('\*', '.*', preg_quote($url, '/'));

        /** @var array{ scheme: string, host: string, port?: int, path?: string} $segments */
        $segments = parse_url($this->page->url());

        $currentUrl = sprintf(
            '%s://%s%s%s',
            $segments['scheme'],
            $segments['host'],
            isset($segments['port']) ? ':'.$segments['port'] : '',
            $segments['path'] ?? ''
        );

        $currentUrl = mb_rtrim($currentUrl, '/');

        $message = "Actual URL [{$currentUrl}] does not equal expected URL [{$url}].";

        expect($currentUrl)->toMatch('/^'.$pattern.'$/u', $message);

        return $this;
    }

    /**
     * Assert that the current URL scheme matches the given scheme.
     */
    public function assertSchemeIs(string $scheme): Webpage
    {
        $pattern = str_replace('\*', '.*', preg_quote($scheme, '/'));

        $actual = parse_url($this->page->url(), PHP_URL_SCHEME) ?? '';

        $message = "Actual scheme [{$actual}] does not equal expected scheme [{$pattern}].";
        expect($actual)->toMatch('/^'.$pattern.'$/u', $message);

        return $this;
    }

    /**
     * Assert that the current URL scheme does not match the given scheme.
     */
    public function assertSchemeIsNot(string $scheme): Webpage
    {
        $actual = parse_url($this->page->url(), PHP_URL_SCHEME) ?? '';

        $message = "Scheme [{$scheme}] should not equal the actual value.";
        expect($actual)->not->toBe($scheme, $message);

        return $this;
    }

    /**
     * Assert that the current URL host matches the given host.
     */
    public function assertHostIs(string $host): Webpage
    {
        $pattern = str_replace('\*', '.*', preg_quote($host, '/'));

        $actual = parse_url($this->page->url(), PHP_URL_HOST) ?? '';

        $message = "Actual host [{$actual}] does not equal expected host [{$pattern}].";
        expect($actual)->toMatch('/^'.$pattern.'$/u', $message);

        return $this;
    }

    /**
     * Assert that the current URL host does not match the given host.
     */
    public function assertHostIsNot(string $host): Webpage
    {
        $actual = parse_url($this->page->url(), PHP_URL_HOST) ?? '';

        $message = "Host [{$host}] should not equal the actual value.";
        expect($actual)->not->toBe($host, $message);

        return $this;
    }

    /**
     * Assert that the current URL port matches the given port.
     */
    public function assertPortIs(string $port): Webpage
    {
        $pattern = str_replace('\*', '.*', preg_quote($port, '/'));

        $actual = (string) (parse_url($this->page->url(), PHP_URL_PORT) ?? '80');

        $message = "Actual port [{$actual}] does not equal expected port [{$pattern}].";

        // Perform an assertion that will always be recorded
        expect(true)->toBeTrue();

        // Check if the port matches the pattern
        if (preg_match('/^'.$pattern.'$/u', $actual) !== 1) {
            throw new \PHPUnit\Framework\ExpectationFailedException($message);
        }

        return $this;
    }

    /**
     * Assert that the current URL port does not match the given port.
     */
    public function assertPortIsNot(string $port): Webpage
    {
        $actual = (string) (parse_url($this->page->url(), PHP_URL_PORT) ?? '80');

        $message = "Port [{$port}] should not equal the actual value.";
        expect($actual)->not->toBe($port, $message);

        return $this;
    }

    /**
     * Assert that the current URL path begins with the given path.
     */
    public function assertPathBeginsWith(string $path): Webpage
    {
        /** @var non-empty-string $actualPath */
        $actualPath = parse_url($this->page->url(), PHP_URL_PATH) ?? '';

        $message = "Actual path [{$actualPath}] does not begin with expected path [{$path}].";

        assert($path !== '', 'Expected path to not be empty.');

        expect($actualPath)->toStartWith($path, $message);

        return $this;
    }

    /**
     * Assert that the current URL path matches the given route.
     *
     * @param  array<string, mixed>  $parameters
     */
    public function assertRoute(string $route, array $parameters = []): Webpage
    {
        if (function_exists('route') === false) {
            throw new RuntimeException('The [route] function is not available. Ensure you are using a framework that provides this function.');
        }

        return $this->assertPathIs(route($route, $parameters, false));
    }

    /**
     * Assert that the current URL path ends with the given path.
     */
    public function assertPathEndsWith(string $path): Webpage
    {
        $actualPath = parse_url($this->page->url(), PHP_URL_PATH) ?? '';

        $message = "Actual path [{$actualPath}] does not end with expected path [{$path}].";

        assert($path !== '', 'Expected path to not be empty.');

        expect($actualPath)->toEndWith($path, $message);

        return $this;
    }

    /**
     * Assert that the current URL path contains the given path.
     */
    public function assertPathContains(string $path): Webpage
    {
        $actualPath = parse_url($this->page->url(), PHP_URL_PATH) ?? '';

        assert(is_string($actualPath), 'Expected actual path to be a string.');

        $message = "Actual path [{$actualPath}] does not contain the expected string [{$path}].";
        expect(str_contains($actualPath, $path))->toBeTrue($message);

        return $this;
    }

    /**
     * Assert that the current path matches the given path.
     */
    public function assertPathIs(string $path): Webpage
    {
        $pattern = str_replace('\*', '.*', preg_quote($path, '/'));

        $actualPath = parse_url($this->page->url(), PHP_URL_PATH) ?? '';

        $message = "Actual path [{$actualPath}] does not equal expected path [{$path}].";
        expect($actualPath)->toMatch('/^'.$pattern.'$/u', $message);

        return $this;
    }

    /**
     * Assert that the current path does not match the given path.
     */
    public function assertPathIsNot(string $path): Webpage
    {
        $actualPath = parse_url($this->page->url(), PHP_URL_PATH) ?? '';

        $message = "Path [{$path}] should not equal the actual value.";
        expect($actualPath)->not->toBe($path, $message);

        return $this;
    }

    /**
     * Assert that the given query string parameter is present and has a given value.
     */
    public function assertQueryStringHas(string $name, ?string $value = null): Webpage
    {
        $output = $this->assertHasQueryStringParameter($name);

        if ($value === null) {
            return $this;
        }

        $parsedOutputName = is_array($output[$name]) ? implode(',', $output[$name]) : $output[$name];

        $message = "Query string parameter [{$name}] had value [{$parsedOutputName}], but expected [{$value}].";
        expect($output[$name])->toBe($value, $message);

        return $this;
    }

    /**
     * Assert that the given query string parameter is missing.
     */
    public function assertQueryStringMissing(string $name): Webpage
    {
        $parsedUrl = parse_url($this->page->url());

        if (! is_array($parsedUrl) || ! array_key_exists('query', $parsedUrl)) {
            expect(true)->toBeTrue();

            return $this;
        }

        parse_str($parsedUrl['query'], $output);

        $message = "Found unexpected query string parameter [{$name}] in [".$this->page->url().'].';
        expect(! isset($output[$name]))->toBeTrue($message);

        return $this;
    }

    /**
     * Assert that the URL's current hash fragment matches the given fragment.
     */
    public function assertFragmentIs(string $fragment): Webpage
    {
        $href = $this->page->evaluate('window.location.href');

        assert(is_string($href), 'Expected href to be a string.');

        $pattern = preg_quote($fragment, '/');

        $href = $this->page->evaluate('window.location.href');

        assert(is_string($href), 'Expected href to be a string.');

        $actualFragment = (string) parse_url($href, PHP_URL_FRAGMENT);

        $message = "Actual fragment [{$actualFragment}] does not equal expected fragment [{$fragment}].";
        expect($actualFragment)->toMatch('/^'.str_replace('\*', '.*', $pattern).'$/u', $message);

        return $this;
    }

    /**
     * Assert that the URL's current hash fragment begins with the given fragment.
     */
    public function assertFragmentBeginsWith(string $fragment): Webpage
    {
        $href = $this->page->evaluate('window.location.href');

        assert(is_string($href), 'Expected href to be a string.');

        $actualFragment = (string) parse_url($href, PHP_URL_FRAGMENT);

        $message = "Actual fragment [{$actualFragment}] does not begin with expected fragment [{$fragment}].";

        assert($fragment !== '', 'Expected fragment to not be empty.');

        expect($actualFragment)->toStartWith($fragment, $message);

        return $this;
    }

    /**
     * Assert that the URL's current hash fragment does not match the given fragment.
     */
    public function assertFragmentIsNot(string $fragment): Webpage
    {
        $href = $this->page->evaluate('window.location.href');

        assert(is_string($href), 'Expected href to be a string.');

        $actualFragment = (string) parse_url($href, PHP_URL_FRAGMENT);

        $message = "Fragment [{$fragment}] should not equal the actual value.";
        expect($actualFragment)->not->toBe($fragment, $message);

        return $this;
    }

    /**
     * Assert that the given query string parameter is present.
     *
     * @return array<int|string, array<array-key, mixed>|string>
     */
    protected function assertHasQueryStringParameter(string $name): array
    {
        /** @var array{ scheme: string, host: string, port?: int, path?: string, query?: string} */
        $segments = parse_url($this->page->url());

        $message = 'Did not see expected query string in ['.$this->page->url().'].';
        expect(isset($segments['query']))->toBeTrue($message);

        assert(isset($segments['query']), 'Query string is not set.');

        parse_str($segments['query'], $output);

        $message = "Did not see expected query string parameter [{$name}] in [".$this->page->url().'].';
        expect(isset($output[$name]))->toBeTrue($message);

        return $output;
    }
}
