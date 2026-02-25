<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LimpiarSistema extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'sistema:limpiar
                            {--con-clientes : Eliminar también clientes y vehículos}
                            {--con-metodos-pago : Eliminar también métodos de pago}
                            {--con-servicios : Eliminar también servicios e inventario}
                            {--todo : Limpiar absolutamente todo excepto usuarios}
                            {--force : No pedir confirmación}';

    /**
     * The console command description.
     */
    protected $description = 'Limpia todos los datos transaccionales del sistema preservando los usuarios';

    public function handle(): int
    {
        $this->newLine();
        $this->line('  <fg=yellow;options=bold>⚠  LIMPIEZA DEL SISTEMA</>');
        $this->newLine();

        $borrarClientes     = $this->option('con-clientes')      || $this->option('todo');
        $borrarMetodos      = $this->option('con-metodos-pago')  || $this->option('todo');
        $borrarServicios    = $this->option('con-servicios')      || $this->option('todo');

        // Mostrar qué se va a borrar
        $this->line('  Se eliminarán los siguientes datos:');
        $this->line('    <fg=red>✗</> Auditoría (audit_logs)');
        $this->line('    <fg=red>✗</> Ítems de ventas (sales_items)');
        $this->line('    <fg=red>✗</> Ventas (sales)');
        $this->line('    <fg=red>✗</> Ítems de órdenes (service_order_items)');
        $this->line('    <fg=red>✗</> Órdenes de servicio (service_orders)');

        if ($borrarClientes) {
            $this->line('    <fg=red>✗</> Vehículos (vehicles)');
            $this->line('    <fg=red>✗</> Clientes (customers)');
        }
        if ($borrarServicios) {
            $this->line('    <fg=red>✗</> Tabla pivot inventario-servicio (inventory_service)');
            $this->line('    <fg=red>✗</> Servicios (services)');
            $this->line('    <fg=red>✗</> Inventario (inventories)');
        }
        if ($borrarMetodos) {
            $this->line('    <fg=red>✗</> Métodos de pago (payment_methods)');
        }

        $this->newLine();
        $this->line('  <fg=green>✓</> Usuarios (users) — <options=bold>NO serán eliminados</>');
        $this->newLine();

        if (!$this->option('force') && !$this->confirm('  ¿Confirmas que quieres limpiar el sistema?', false)) {
            $this->line('  <fg=yellow>Operación cancelada.</> No se eliminó nada.');
            return self::FAILURE;
        }

        $this->newLine();
        $this->line('  Limpiando...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Siempre se limpian los datos transaccionales
        DB::table('audit_logs')->truncate();
        $this->line('    ✓ audit_logs');

        DB::table('sales_items')->truncate();
        $this->line('    ✓ sales_items');

        DB::table('sales')->truncate();
        $this->line('    ✓ sales');

        DB::table('service_order_items')->truncate();
        $this->line('    ✓ service_order_items');

        DB::table('service_orders')->truncate();
        $this->line('    ✓ service_orders');

        // Opcionales
        if ($borrarClientes) {
            DB::table('vehicles')->truncate();
            $this->line('    ✓ vehicles');
            DB::table('customers')->truncate();
            $this->line('    ✓ customers');
        }

        if ($borrarServicios) {
            DB::table('inventory_service')->truncate();
            $this->line('    ✓ inventory_service');
            DB::table('services')->truncate();
            $this->line('    ✓ services');
            DB::table('inventories')->truncate();
            $this->line('    ✓ inventories');
        }

        if ($borrarMetodos) {
            DB::table('payment_methods')->truncate();
            $this->line('    ✓ payment_methods');
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->newLine();
        $this->line('  <fg=green;options=bold>✓ Sistema limpiado correctamente. Los usuarios se mantienen intactos.</>');
        $this->newLine();

        return self::SUCCESS;
    }
}
