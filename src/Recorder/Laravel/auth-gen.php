<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Cookie\CookieValuePrefix;
use Illuminate\Support\Str;

define('LARAVEL_START', microtime(true));

[$rootPath, $host, $storageFile] = array_slice($argv, 1, 3);

require $rootPath . '/vendor/autoload.php';

$app = require_once $rootPath . '/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$userModel = config('auth.providers.users.model', User::class);

$manager = app('session');
$store = $manager->driver();
$store->setId(Str::random(40));
$store->start();

$user = $userModel::factory()->create();
app('auth')->guard()->setUser($user);
$store->put(app('auth')->guard()->getName(), $user->getAuthIdentifier());
$store->put('password_hash_' . app('auth')->getDefaultDriver(), $user->getAuthPassword());
$store->save();

$sessionId = $store->getId();
$cookieName = config('session.cookie');
$encrypter = app('encrypter');
$prefix = CookieValuePrefix::create($cookieName, $encrypter->getKey());
$encrypted = $encrypter->encrypt($prefix . $sessionId, false);

file_put_contents($storageFile, json_encode([
    'cookies' => [[
        'name' => $cookieName,
        'value' => $encrypted,
        'domain' => $host,
        'path' => '/',
        'expires' => -1,
        'httpOnly' => true,
        'secure' => false,
        'sameSite' => 'Lax',
    ]],
    'origins' => [],
]));

echo $userModel;
