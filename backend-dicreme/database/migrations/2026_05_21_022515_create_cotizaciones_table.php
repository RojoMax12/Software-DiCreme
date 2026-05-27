<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario_dicreme')->nullable();
            $table->unsignedBigInteger('id_distribuidor');
            $table->unsignedBigInteger('id_estado_cotizacion');
            $table->date('fecha_creacion');
            $table->time('hora_creacion');
            $table->integer('total_cotizacion');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cotizaciones');
    }
};
