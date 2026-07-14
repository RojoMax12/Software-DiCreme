<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TelefonoChilenoRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Convertimos a string explícitamente y quitamos cualquier espacio o carácter invisible
        $cleanPhone = preg_replace('/[^0-9]/', '', (string)$value);
        
        // Si el usuario envió el código de país (569), lo normalizamos a 9 dígitos
        // Ejemplo: si envían 56947936795 (11 dígitos), se convierte en 947936795
        if (strlen($cleanPhone) === 11 && strpos($cleanPhone, '569') === 0) {
            $cleanPhone = substr($cleanPhone, 2);
        }
        
        // Validación: debe empezar con 9 y tener 9 dígitos
        if (!preg_match('/^9\d{8}$/', $cleanPhone)) {
            $fail('El campo :attribute debe ser un número celular chileno válido.');
        }
    }
}