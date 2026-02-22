<?php

use Illuminate\Support\Facades\Mail;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Enviando correo de prueba a stevenpernia58@gmail.com...\n";

try {
    Mail::raw('¡Conexión establecida! Si recibes este correo, Resend está funcionando correctamente en tu proyecto de Laravel para AUTODETALING LUNA C.A.', function ($message) {
        $message->to('stevenpernia58@gmail.com')
                ->subject('Prueba de Conexión Resend - Éxito');
    });
    echo "\n[OK] El correo ha sido enviado. Por favor, revisa tu bandeja de entrada (incluyendo SPAM).\n";
} catch (\Exception $e) {
    echo "\n[ERROR] No se pudo enviar el correo: " . $e->getMessage() . "\n";
    echo "Asegúrate de haber ejecutado 'install_resend.bat' para instalar el SDK.\n";
}
