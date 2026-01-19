<?php

use Illuminate\Contracts\Console\Kernel;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

$users = \App\Models\User::all();

$data = $users->map(function ($user) {
    return [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'role' => $user->role,
        'status' => $user->status ? 'Active' : 'Inactive',
        'created_at' => $user->created_at->format('Y-m-d H:i:s'),
    ];
});

echo json_encode($data, JSON_PRETTY_PRINT);
