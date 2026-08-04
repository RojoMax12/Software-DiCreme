<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\Usuario_dicreme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear rol 1 (Admin) si no existe
        Rol::create([
            'id' => 1,
            'nombre_rol' => 'Admin'
        ]);
    }

    public function test_login_exitoso_retorna_token_jwt(): void
    {
        $user = Usuario_dicreme::create([
            'id_rol' => 1,
            'nombre_usuario' => 'admin_test',
            'correo' => 'admin@test.com',
            'contrasena' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'correo' => 'admin@test.com',
            'contrasena' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'access_token',
                     'token_type',
                     'user'
                 ]);
    }

    public function test_login_fallido_con_credenciales_incorrectas(): void
    {
        Usuario_dicreme::create([
            'id_rol' => 1,
            'nombre_usuario' => 'admin_test',
            'correo' => 'admin@test.com',
            'contrasena' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'correo' => 'admin@test.com',
            'contrasena' => 'incorrecta',
        ]);

        $response->assertStatus(401);
    }

    public function test_acceso_denegado_a_ruta_protegida_sin_token(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401);
    }
}
