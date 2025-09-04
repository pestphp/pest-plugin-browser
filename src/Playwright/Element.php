<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

use Pest\Browser\Playwright\Concerns\InteractsWithPlaywright;

/**
 * @internal
 */
final readonly class Element
{
    use InteractsWithPlaywright;

    /**
     * Creates a new element instance.
     */
    public function __construct(
        private string $guid,
    ) {
        //
    }

    /**
     * Tap the element (touch screen interaction).
     *
     * @param  array<string, mixed>|null  $options
     */
    public function tap(?array $options = null): void
    {
        $this->processVoidResponse($this->sendMessage('tap', $options ?? []));
    }

    /**
     * Get the text content of the element.
     */
    public function textContent(): ?string
    {
        return $this->processNullableStringResponse($this->sendMessage('textContent'));
    }

    /**
     * Get the inner text of the element.
     */
    public function innerText(): string
    {
        return $this->processStringResponse($this->sendMessage('innerText'));
    }

    /**
     * Get the bounding box of the element.
     *
     * @return array{x: float, y: float, width: float, height: float}|null
     */
    public function boundingBox(): ?array
    {
        $result = $this->processResultResponse($this->sendMessage('boundingBox'));

        if ($result === null) {
            return null;
        }

        if (! is_array($result) || ! isset($result['x'], $result['y'], $result['width'], $result['height'])) {
            return null;
        }

        return [
            'x' => is_numeric($result['x']) ? (float) $result['x'] : 0.0,
            'y' => is_numeric($result['y']) ? (float) $result['y'] : 0.0,
            'width' => is_numeric($result['width']) ? (float) $result['width'] : 0.0,
            'height' => is_numeric($result['height']) ? (float) $result['height'] : 0.0,
        ];
    }

    /**
     * Take a screenshot of the element.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function screenshot(?array $options = null): string
    {
        return $this->processBinaryResponse($this->sendMessage('screenshot', $options ?? []));
    }

    /**
     * Scroll element into view if needed.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function scrollIntoViewIfNeeded(?array $options = null): void
    {
        $this->processVoidResponse($this->sendMessage('scrollIntoViewIfNeeded', $options ?? []));
    }

    /**
     * Wait for element to reach a specific state.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function waitForElementState(string $state, ?array $options = null): void
    {
        $params = array_merge(['state' => $state], $options ?? []);
        $this->processVoidResponse($this->sendMessage('waitForElementState', $params));
    }

    /**
     * Select text in the element.
     *
     * @param  array<string, mixed>|null  $options
     */
    public function selectText(?array $options = null): void
    {
        $this->processVoidResponse($this->sendMessage('selectText', $options ?? []));
    }

    /**
     * Get the content frame for iframe elements.
     */
    public function contentFrame(): ?object
    {
        $frameGuid = $this->processFrameCreationResponse($this->sendMessage('contentFrame'));

        return $frameGuid !== null ? (object) ['guid' => $frameGuid] : null;
    }

    /**
     * Get the owner frame of the element.
     */
    public function ownerFrame(): ?object
    {
        $frameGuid = $this->processFrameCreationResponse($this->sendMessage('ownerFrame'));

        return $frameGuid !== null ? (object) ['guid' => $frameGuid] : null;
    }
}
