<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

Route::get('dashboard', function () {
    App\Jobs\ProcessPodcast::dispatch();

    return Blade::render(<<<'BLADE'
        <div>
            Hi {{ auth()->user()?->name ?? 'Guest'}}!
        </div>
        BLADE,
    );
})->name('dashboard');

Route::get('/queue', function () {
    App\Jobs\ProcessPodcast::dispatch();

    return [
        'message' => 'Queue job dispatched successfully.',
        'status' => 'success',
    ];
})->name('queue');

Route::get('/auth', function () {
    return [
        'user_id' => auth()->id(),
        'user_name' => auth()->user()?->name ?? 'Guest',
        'message' => 'Hello, '.(auth()->user()?->name ?? 'Guest').'!',
    ];
})->name('auth');

Route::get('/logout', function () {
    auth()->logout();

    session()->invalidate();
    session()->regenerateToken();

    return to_route('auth');
})->name('logout');

Route::get('/logout-and-destroy-session', function () {
    auth()->logout();

    return redirect()->route('auth');
})->name('logout-and-destroy-session');

Route::get('/database', function () {
    return [
        'users' => User::all(),
        'database' => [
            'driver' => config('database.default'),
            'name' => config('database.connections.'.config('database.default').'.database'),
        ],
    ];
})->name('database');

Route::get('/environment-variables', function () {
    return [
        'APP_ENV' => config('app.env'),
        'APP_DEBUG' => config('app.debug'),
        'TEST_TOKEN' => $_SERVER['TEST_TOKEN'] ?? false,
    ];
})->name('environment-variables');

Route::get('/', function () {
    return view('index');
})->name('home');

Route::get('/test/{page}', function ($page) {
    return view('test-pages.'.$page);
})->name('test-page');
