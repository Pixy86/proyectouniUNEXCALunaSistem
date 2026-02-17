<?php

use Illuminate\Contracts\Console\Kernel;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

$user = \App\Models\User::where('email', 'usuarioprueba@example.com')->first();

if ($user) {
    $user->role = 'Administrador';
    $user->estado = true; // Assuming 'estado' is the column name based on previous file views (ListUser.php uses 'estado')
    $user->save();
    echo "User '{$user->name}' updated successfully.\nRole: {$user->role}\nStatus: Active\n";
} else {
    echo "User not found.\n";
}
