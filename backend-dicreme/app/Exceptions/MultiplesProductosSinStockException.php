<?php

namespace App\Exceptions;

use Exception;

class MultiplesProductosSinStockException extends Exception
{
    protected array $productosFaltantes;

    public function __construct(array $productosFaltantes)
    {
        $count = count($productosFaltantes);
        $message = "Stock insuficiente en {$count} " . ($count === 1 ? 'producto' : 'productos') . " para completar la cotización.";
        parent::__construct($message);
        $this->productosFaltantes = $productosFaltantes;
    }

    public function getProductosFaltantes(): array
    {
        return $this->productosFaltantes;
    }
}
