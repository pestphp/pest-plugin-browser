<?php

declare(strict_types=1);

use Pest\Browser\TestResult;

describe('TestResult class', function () {
    test('ok method returns if test suite passes', function ($json, $expected) {
        $testResult = new TestResult(json_decode($json, true));
        expect($testResult->ok())->toBe($expected);
    })->with([
        ['{"suites":[{"specs":[{"ok":true}]}]}', true],
        ['{"suites":[{"specs":[{"ok":false}]}]}', false],
    ]);

    test('method getFirstFailedTest throws exception when no failed tests found', function () {
        $testResult = new TestResult([]);
        $testResult->getFailedLine();
    })->throws(RuntimeException::class, 'No failed tests found in the test result');

    test('method getFirstFailedTest returns the first failed test', function () {
        $testResult = new TestResult(json_decode(
            '{"suites":[{"specs":[{"ok":false,"tests":[{"status":"unexpected"}]}]}]}',
            true
        ));

        $failedTest = $testResult->getFirstFailedTest();

        expect($failedTest)->toBe(['status' => 'unexpected']);
    });

    test('method getFailedLine returns the line number of the first failed test', function () {
        $compiledOperation = 'testoperation';
        file_put_contents('/tmp/testtmp', $compiledOperation);

        $testResult = new TestResult(json_decode(
            '{"suites":[{"specs":[{"ok":false,"tests":[{"status":"unexpected","results":[{"errorLocation":{"file":"/tmp/testtmp","line":1}}]}]}]}]}',
            true
        ));

        $failedLine = $testResult->getFailedLine();

        unlink('/tmp/testtmp');

        expect($failedLine)->toBe($compiledOperation);
    });

    test('method getFailedLine throws exception when cannot read file line', function () {
        $testResult = new TestResult(json_decode(
            '{"suites":[{"specs":[{"ok":false,"tests":[{"status":"unexpected","results":[{"errorLocation":{"file":"/tmp/testtmp","line":1}}]}]}]}]}',
            true
        ));

        $testResult->getFailedLine();
    })->throws(RuntimeException::class);
});
