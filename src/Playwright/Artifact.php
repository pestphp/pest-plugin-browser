<?php

declare(strict_types=1);

namespace Pest\Browser\Playwright;

/**
 * @internal
 */
final readonly class Artifact
{
    use Concerns\InteractsWithPlaywright;

    /**
     * The size of each chunk read from the artifact's stream, in bytes.
     */
    private const int CHUNK_SIZE = 1024 * 1024;

    /**
     * Creates a new artifact instance.
     */
    public function __construct(
        private string $guid,
    ) {
        //
    }

    /**
     * Reads the artifact's contents, and deletes it from the Playwright server.
     */
    public function contents(): string
    {
        $streamGuid = null;

        /** @var array{result?: array{stream?: array{guid?: string}}} $message */
        foreach ($this->sendMessage('saveAsStream') as $message) {
            if (isset($message['result']['stream']['guid'])) {
                $streamGuid = $message['result']['stream']['guid'];
            }
        }

        $contents = '';

        if ($streamGuid !== null) {
            $client = Client::instance();

            do {
                $chunk = '';

                /** @var array{result?: array{binary?: string}} $message */
                foreach ($client->execute($streamGuid, 'read', ['size' => self::CHUNK_SIZE]) as $message) {
                    if (isset($message['result']['binary'])) {
                        $chunk = (string) base64_decode($message['result']['binary'], true);
                    }
                }

                $contents .= $chunk;
            } while ($chunk !== '');

            $this->processVoidResponse($client->execute($streamGuid, 'close'));
        }

        $this->processVoidResponse($this->sendMessage('delete'));

        return $contents;
    }
}
