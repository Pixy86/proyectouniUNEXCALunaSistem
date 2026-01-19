<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
);

echo "<h1>Iniciando Migración...</h1>";
try {
    \Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--seed' => true]);
    echo "<pre>" . \Illuminate\Support\Facades\Artisan::output() . "</pre>";
    echo "<h1>✅ Migración Completada Exitosamente!</h1>";
} catch (\Exception $e) {
    echo "<h1>❌ Error:</h1>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
