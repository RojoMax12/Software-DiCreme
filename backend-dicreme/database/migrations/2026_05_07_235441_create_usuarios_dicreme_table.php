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
        Schema::create('usuarios_dicreme', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_usuario')->unique();
            $table->string('correo_electronico')->unique();
            $table->string('contrasena');
            $table->unsignedBigInteger('id_rol');
            $table->boolean('estado_usuario')->default(true);
            $table->string('foto_perfil')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios_dicreme');
    }
};
