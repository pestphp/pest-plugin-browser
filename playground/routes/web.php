<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
})->name('home');

Route::get('/test/{page}', function ($page) {
    return view('test-pages.'.$page);
})->name('test-page');
