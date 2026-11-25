<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Session\DatabaseSessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Schema;
use Pest\Browser\Drivers\ClientAuthentication;

beforeEach(function (): void {
    Schema::dropIfExists('sessions');
    Schema::create('sessions', function (Blueprint $table): void {
        $table->string('id')->primary();
        $table->foreignId('user_id')->nullable();
        $table->string('ip_address', 45)->nullable();
        $table->text('user_agent')->nullable();
        $table->longText('payload');
        $table->integer('last_activity');
    });
});

it('keeps the first database save of the test session after a frontend request saved its own session', function (): void {
    $handler = fn (): DatabaseSessionHandler => new DatabaseSessionHandler(app('db')->connection(), 'sessions', 120, app());
    $session = new Store('pest_session', $handler());

    $session->put('owner', 'test');
    $testId = $session->getId();

    $authentication = new ClientAuthentication(null, $session);
    $authentication->isolate();

    $session->start();
    $session->put('client', 'frontend');
    $session->save();

    $authentication->restore();

    $session->save();

    $reloaded = new Store('pest_session', $handler(), $testId);
    $reloaded->start();

    expect($reloaded->get('owner'))->toBe('test');
});
