<?php
namespace App\Observers;

use App\Models\Lote;
use App\Repositories\BodegaRepository;

class LoteObserver
{
    protected $bodegaRepository;

    public function __construct(BodegaRepository $bodegaRepository)
    {
        $this->bodegaRepository = $bodegaRepository;
    }

    public function created(Lote $lote): void
    {
        $this->bodegaRepository->updateCantidadElementos($lote->id_bodega);
    }

    public function updated(Lote $lote): void
    {
        $this->bodegaRepository->updateCantidadElementos($lote->id_bodega);
    }

    public function deleted(Lote $lote): void
    {
        $this->bodegaRepository->updateCantidadElementos($lote->id_bodega);
    }
}