<?php

declare(strict_types=1);

use Pest\Browser\TestResult;

describe('TestResult', function () {
    it('returns if the test passed', function ($output, $expected) {
        $result = new TestResult($output);

        expect($result->ok())->toBe($expected);
    })->with([
        [['suites' => [['specs' => [['ok' => true]]]]], true],
        [['suites' => [['specs' => [['ok' => false]]]]], false],
        [[], false],
    ]);

    it('returns failed line', function () {
        $now = time();
        $tempFile = "/tmp/{$now}.txt";
        $expectedLineContent = 'expected line content';

        file_put_contents($tempFile, $expectedLineContent);

        $output = [
            'suites' => [
                [
                    'specs' => [
                        [
                            'ok' => false,
                            'tests' => [
                                [
                                    'status' => 'unexpected',
                                    'results' => [
                                        [
                                            'errorLocation' => [
                                                'file' => $tempFile,
                                                'line' => 1,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $result = new TestResult($output);

        expect($result->getFailedLine())->toBe($expectedLineContent);

        unlink($tempFile);
    });

    it('returns failed browser', function () {
        $expectedBrowser = 'chromium';

        $output = [
            'suites' => [
                [
                    'specs' => [
                        [
                            'ok' => false,
                            'tests' => [
                                [
                                    'status' => 'unexpected',
                                    'projectName' => $expectedBrowser,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $result = new TestResult($output);

        expect($result->getFailedBrowser())->toBe($expectedBrowser);
    });
});
