<?php

declare(strict_types=1);

use Pest\Browser\Enums\TraceMode;
use Pest\Browser\Support\Trace;
use Pest\Browser\Support\TraceReporter;
use Pest\Support\Ci;
use Pest\Support\Container;
use Pest\TestSuite;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

beforeEach(function (): void {
    $this->originalRootPath = TestSuite::getInstance()->rootPath;
    $this->originalOutput = Container::getInstance()->get(OutputInterface::class);
    $this->originalCiEnv = [];

    foreach ([...Ci::ENVIRONMENT_VARIABLES, 'GITHUB_STEP_SUMMARY'] as $env) {
        $this->originalCiEnv[$env] = getenv($env);
        putenv($env);
    }

    $this->tempDir = sys_get_temp_dir().'/pest-browser-trace-reporter-'.uniqid('', true);
    mkdir($this->tempDir, 0755, true);

    TestSuite::getInstance()->rootPath = $this->tempDir;
});

afterEach(function (): void {
    Trace::cleanup();
    TestSuite::getInstance()->rootPath = $this->originalRootPath;
    Container::getInstance()->add(OutputInterface::class, $this->originalOutput);

    foreach ($this->originalCiEnv as $env => $value) {
        putenv($value === false ? $env : $env.'='.$value);
    }

    @unlink($this->tempDir.'/summary.md');
    @rmdir($this->tempDir.'/tests/Browser');
    @rmdir($this->tempDir.'/tests');
    @rmdir($this->tempDir);
});

function createReporterTraceZip(): string
{
    $tempFile = tempnam(sys_get_temp_dir(), 'pest-trace-reporter-zip-');
    $zip = new ZipArchive();
    $zip->open($tempFile, ZipArchive::OVERWRITE);
    $zip->addFromString('trace.trace', '{}');
    $zip->close();

    $contents = (string) file_get_contents($tempFile);
    @unlink($tempFile);

    return $contents;
}

it('lists saved traces locally when all tests pass in on mode', function (): void {
    $output = new BufferedOutput();
    Container::getInstance()->add(OutputInterface::class, $output);

    $archive = createReporterTraceZip();
    Trace::save(Trace::path('first-passing'), $archive, [], failed: false);
    Trace::save(Trace::path('second-passing'), $archive, [], failed: false);

    TraceReporter::report(open: true, mode: TraceMode::ON);

    $rendered = $output->fetch();

    expect($rendered)->toContain('Saved 2 traces to tests/Browser/Traces.')
        ->and($rendered)->toContain('tests/Browser/Traces/first-passing.zip')
        ->and($rendered)->toContain('tests/Browser/Traces/second-passing.zip')
        ->and($rendered)->toContain('npx playwright show-trace <trace>');
});

it('does not print passing trace list when failures exist and opening is disabled', function (): void {
    $output = new BufferedOutput();
    Container::getInstance()->add(OutputInterface::class, $output);

    $archive = createReporterTraceZip();
    Trace::save(Trace::path('passing-test'), $archive, [], failed: false);
    Trace::save(Trace::path('failing-test'), $archive, [], failed: true);

    TraceReporter::report(open: false, mode: TraceMode::ON);

    expect($output->fetch())->toBe('');
});

it('writes failed-only wording to the GitHub step summary in retain-on-failure mode', function (): void {
    $summaryFile = $this->tempDir.'/summary.md';
    putenv('CI=true');
    putenv('GITHUB_STEP_SUMMARY='.$summaryFile);

    $archive = createReporterTraceZip();
    Trace::save(Trace::path('failing-test'), $archive, [], failed: true);

    TraceReporter::report(open: true, mode: TraceMode::RETAIN_ON_FAILURE);

    $summary = (string) file_get_contents($summaryFile);

    expect($summary)->toContain('The following failed browser tests recorded a trace.')
        ->and($summary)->toContain('- `tests/Browser/Traces/failing-test.zip`');
});

it('writes all-tests wording to the GitHub step summary in on mode', function (): void {
    $summaryFile = $this->tempDir.'/summary.md';
    putenv('CI=true');
    putenv('GITHUB_STEP_SUMMARY='.$summaryFile);

    $archive = createReporterTraceZip();
    Trace::save(Trace::path('passing-test'), $archive, [], failed: false);
    Trace::save(Trace::path('failing-test'), $archive, [], failed: true);

    TraceReporter::report(open: true, mode: TraceMode::ON);

    $summary = (string) file_get_contents($summaryFile);

    expect($summary)->toContain('The following browser tests recorded a trace.')
        ->and($summary)->toContain('- `tests/Browser/Traces/passing-test.zip`')
        ->and($summary)->toContain('- `tests/Browser/Traces/failing-test.zip`');
});
