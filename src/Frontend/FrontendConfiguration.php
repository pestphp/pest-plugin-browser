<?php

declare(strict_types=1);

namespace Pest\Browser\Frontend;

/**
 * @internal
 */
final readonly class FrontendConfiguration
{
    /**
     * Creates a new frontend configuration instance.
     */
    public function __construct(
        private Frontend $frontend,
    ) {
        //
    }

    /**
     * Gives the URL of the bridge to the frontend in the given environment variables, such as "NEXT_PUBLIC_API_URL".
     */
    public function backendUrlEnv(string ...$names): self
    {
        $this->frontend->backendUrlEnv = array_values(array_unique([...$this->frontend->backendUrlEnv, ...$names]));

        return $this;
    }

    /**
     * Gives the frontend process extra environment variables. The values replace "{url}", "{port}" and "{bridge}".
     *
     * @param  array<string, string>  $variables
     */
    public function env(array $variables): self
    {
        $this->frontend->environment = [...$this->frontend->environment, ...$variables];

        return $this;
    }

    /**
     * Sets the fixed port of the bridge that forwards the backend requests of the frontend to the test process.
     */
    public function bridgePort(int $port): self
    {
        $this->frontend->bridgePort = $port;

        return $this;
    }

    /**
     * Sets the request headers in which the bridge looks for the "-w<lane>" host that names the test process.
     */
    public function routeBy(string ...$headers): self
    {
        $this->frontend->routeHeaders = array_values($headers);

        return $this;
    }

    /**
     * Forwards the host of the lane, such as "acme-w4123.localhost:3101", to the backend as its "Host" header.
     */
    public function forwardLaneHost(bool $forward = true): self
    {
        $this->frontend->forwardLaneHost = $forward;

        return $this;
    }

    /**
     * Sets the subdomain that carries the lane in the URL of the frontend, such as "acme" for "acme-w4123.localhost".
     */
    public function subdomain(string $subdomain): self
    {
        $this->frontend->subdomain = $subdomain;

        return $this;
    }

    /**
     * Sets the path that must answer without a server error before the tests use the frontend.
     */
    public function readyPath(string $path): self
    {
        $this->frontend->readyPath = $path;

        return $this;
    }

    /**
     * Sets the seconds the frontend has to become ready.
     */
    public function timeout(int $seconds): self
    {
        $this->frontend->timeout = $seconds;

        return $this;
    }

    /**
     * Verifies that the frontend forwards the given path prefix, such as "/api", to this bridge before the tests use it.
     */
    public function verifyBridgeThrough(string $prefix): self
    {
        $this->frontend->bridgePrefix = $prefix;

        return $this;
    }
}
