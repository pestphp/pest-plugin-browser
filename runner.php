#!/usr/bin/env php
<?php

declare(strict_types=1);

use Ratchet\RFC6455\Messaging\Message;

require __DIR__ . '/vendor/autoload.php';

$browser = null;
$context = null;
$page = null;

\Ratchet\Client\connect('ws://localhost:8099')->then(function($conn) use (&$browser, &$context, &$page) {
    $messageId = 1;

    $conn->on('message', function( Message $msg) use ($conn, &$messageId, &$browser, &$context, &$page) {

        $response = json_decode($msg->getContents(), true);

        echo "Received: " . json_encode($response, JSON_PRETTY_PRINT) . "\n";

        // Check for errors
        if (isset($response['error'])) {
            echo "Error: " . $response['error']['error']['message'] . "\n";
            $conn->close();
            return;
        }

        // Store the guid results for future use
        if (isset($response['result']['guid'])) {
            switch($messageId) {
                case 2:
                    $browser = $response['result']['guid'];
                    break;
                case 3:
                    $context = $response['result']['guid'];
                    break;
                case 4:
                    $page = $response['result']['guid'];
                    break;
            }
        }

        switch($messageId) {
            case 1: // After initialization
                $conn->send(json_encode([
                    'id' => ++$messageId,
                    'method' => 'browserType.launch',
                    'params' => [
                        'browserType' => 'chromium'
                    ]
                ]));
                break;

            case 2: // After browser launch
                $conn->send(json_encode([
                    'id' => ++$messageId,
                    'method' => 'browser.newContext',
                    'params' => [
                    ],
                    'guid' => $browser
                ]));
                break;

            case 3: // After context creation
                $conn->send(json_encode([
                    'id' => ++$messageId,
                    'method' => 'browserContext.newPage',
                    'params' => [],
                    'guid' => $context
                ]));
                break;

            case 4: // After page creation
                $conn->send(json_encode([
                    'id' => ++$messageId,
                    'method' => 'page.goto',
                    'params' => [
                        'url' => 'https://laravel.com',
                        'waitUntil' => 'networkidle'
                    ],
                    'guid' => $page
                ]));
                break;

            case 5: // After navigation
                $conn->send(json_encode([
                    'id' => ++$messageId,
                    'method' => 'page.textContent',
                    'params' => [
                        'selector' => 'body'
                    ],
                    'guid' => $page
                ]));
                break;

            case 6: // After getting text content
                if (isset($response['result']) && str_contains($response['result'], 'Laravel')) {
                    echo "✅ Test passed: Found 'Laravel' text on the page\n";
                } else {
                    echo "❌ Test failed: 'Laravel' text not found\n";
                }

                // Close context (this will also close the page)
                $conn->send(json_encode([
                    'id' => ++$messageId,
                    'method' => 'browserContext.close',
                    'params' => [],
                    'guid' => $context
                ]));
                break;

            case 7: // After context close
                // Close browser
                $conn->send(json_encode([
                    'id' => ++$messageId,
                    'method' => 'browser.close',
                    'params' => [],
                    'guid' => $browser
                ]));
                break;

            case 8: // After browser close
                $conn->close();
                break;
        }
    });

    // Start the process
    $conn->send(json_encode([
        'id' => $messageId,
        'method' => 'initialize',
        'params' => [
            'sdkLanguage' => 'php'
        ]
    ]));

}, function ($e) {
    echo "Could not connect: {$e->getMessage()}\n";
});
