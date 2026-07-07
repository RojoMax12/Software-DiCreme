<?php

namespace App\Services;
use App\Repositories\Usuario_distribuidoresRepository;
use App\Repositories\RolRepository;
use Illuminate\Support\Facades\Hash;

class Usuario_distribuidoresServices
{
    protected $usuarioDistribuidoresRepository;
    protected $rolRepository;

    public function __construct(Usuario_distribuidoresRepository $usuarioDistribuidoresRepository, RolRepository $rolRepository)
    {
        $this->usuarioDistribuidoresRepository = $usuarioDistribuidoresRepository;
        $this->rolRepository = $rolRepository;
    }

    public function getAllUsuariosDistribuidores()
    {
        return $this->usuarioDistribuidoresRepository->getAllUsuariosDistribuidores();
    }

    public function getUsuarioDistribuidorById($id)
    {
        return $this->usuarioDistribuidoresRepository->getUsuarioDistribuidorById($id);
    }

    public function createUsuarioDistribuidor($data)
    {   
        $data['estado_usuario'] = $data['estado_usuario'] ?? true;
        
        $rolDistribuidor = $this->rolRepository->getRoleByName('Distribuidor');
        $data['id_rol'] = $rolDistribuidor->id;
        $data['contrasena'] = Hash::make($data['contrasena']);

        return $this->usuarioDistribuidoresRepository->createUsuarioDistribuidor($data);
    }

    public function updateUsuarioDistribuidor($id, $data)
    {
        return $this->usuarioDistribuidoresRepository->updateUsuarioDistribuidor($id, $data);
    }

    public function deleteUsuarioDistribuidor($id)
    {
        return $this->usuarioDistribuidoresRepository->deleteUsuarioDistribuidor($id);
    }

    public function activarydesactivar($id)
    {
        return $this->usuarioDistribuidoresRepository->activarydesactivar($id);
    }
}