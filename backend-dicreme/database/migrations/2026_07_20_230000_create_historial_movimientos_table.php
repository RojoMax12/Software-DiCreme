<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('historial_movimientos')) {
            Schema::create('historial_movimientos', function (Blueprint $table) {
                $table->id();
                $table->string('tipo_entidad'); // 'lote', 'usuario', 'producto'
                $table->unsignedBigInteger('id_entidad')->nullable();
                $table->string('accion'); // 'creacion', 'modificacion', 'activacion', 'desactivacion', 'liberacion', 'cambio_precio', etc.
                $table->text('descripcion');
                $table->string('usuario_responsable')->nullable();
                $table->json('detalles_json')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_movimientos');
    }
};
