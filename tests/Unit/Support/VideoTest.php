<?php

declare(strict_types=1);

use Pest\Browser\Support\Video;

it('deletes the video when the test passes', function (): void {
    $tempDir = sys_get_temp_dir().'/pest-video-test-'.uniqid('', true);
    mkdir($tempDir, 0755, true);
    $videoFile = $tempDir.'/video.webm';
    file_put_contents($videoFile, 'fake-video-data');

    Video::handleRecording($tempDir, 'my_test', false);

    expect(file_exists($videoFile))->toBeFalse();
    expect(is_dir($tempDir))->toBeFalse();
});

it('moves the video to the videos directory when the test fails', function (): void {
    $tempDir = sys_get_temp_dir().'/pest-video-test-'.uniqid('', true);
    mkdir($tempDir, 0755, true);
    $videoFile = $tempDir.'/video.webm';
    file_put_contents($videoFile, 'fake-video-data');

    Video::handleRecording($tempDir, 'my_failed_test', true);

    $destFile = Video::dir().'/my_failed_test.webm';

    expect(file_exists($destFile))->toBeTrue();
    expect(file_exists($videoFile))->toBeFalse();
    expect(is_dir($tempDir))->toBeFalse();

    @unlink($destFile);
});

it('does nothing when the temp directory has no webm files', function (): void {
    $tempDir = sys_get_temp_dir().'/pest-video-test-'.uniqid('', true);
    mkdir($tempDir, 0755, true);

    Video::handleRecording($tempDir, 'my_test', true);

    // No exception thrown and temp dir is cleaned up
    expect(is_dir($tempDir))->toBeFalse();
});

it('overwrites an existing video when the test fails again', function (): void {
    $tempDir = sys_get_temp_dir().'/pest-video-test-'.uniqid('', true);
    mkdir($tempDir, 0755, true);
    $videoFile = $tempDir.'/video.webm';
    file_put_contents($videoFile, 'new-video-data');

    // Pre-create a file with the expected destination name
    if (is_dir(Video::dir()) === false) {
        mkdir(Video::dir(), 0755, true);
    }
    $destFile = Video::dir().'/overwrite_test.webm';
    file_put_contents($destFile, 'old-video-data');

    Video::handleRecording($tempDir, 'overwrite_test', true);

    // Destination must contain the new content
    expect(file_exists($destFile))->toBeTrue();
    expect(file_get_contents($destFile))->toBe('new-video-data');

    // No timestamp-suffixed files should exist
    $extras = glob(Video::dir().'/overwrite_test-*.webm');
    expect($extras)->toBeEmpty();

    @unlink($destFile);
});

it('cleans up extra videos when more than one webm is present', function (): void {
    $tempDir = sys_get_temp_dir().'/pest-video-test-'.uniqid('', true);
    mkdir($tempDir, 0755, true);

    file_put_contents($tempDir.'/first.webm', 'video-1');
    file_put_contents($tempDir.'/second.webm', 'video-2');

    Video::handleRecording($tempDir, 'multi_page_test', true);

    $destFile = Video::dir().'/multi_page_test.webm';
    expect(file_exists($destFile))->toBeTrue();

    // Extra file must be gone
    expect(file_exists($tempDir.'/second.webm'))->toBeFalse();

    @unlink($destFile);
});
