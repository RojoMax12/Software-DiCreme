<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RutChilenoRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // 1. Limpiar el RUT (quitar puntos y guión)
        $rutLimpio = preg_replace('/[^kK0-9]/', '', $value);
        
        if (strlen($rutLimpio) < 2) {
            $fail('El campo :attribute no es un RUT válido.');
            return;
        }

        // 2. Extraer número y dígito verificador
        $dv = substr($rutLimpio, -1);
        $numero = substr($rutLimpio, 0, -1);
        
        // 3. Calcular dígito verificador esperado
        $i = 2;
        $suma = 0;
        for ($j = strlen($numero) - 1; $j >= 0; $j--) {
            if ($i == 8) $i = 2;
            $suma += $numero[$j] * $i;
            $i++;
        }

        $dvEsperado = 11 - ($suma % 11);
        if ($dvEsperado == 11) $dvEsperado = 0;
        if ($dvEsperado == 10) $dvEsperado = 'K';

        // 4. Validar
        if (strtoupper($dv) !== (string)$dvEsperado) {
            $fail('El campo :attribute no es un RUT válido o el dígito verificador no coincide.');
        }
    }
}