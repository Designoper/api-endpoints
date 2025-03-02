<?php

declare(strict_types=1);

abstract class Sanitizer
{
    protected function __construct()
    {
        $this->sanitizeGlobals();
    }

    private function sanitizeValue(mixed $value): string|array
    {
        if (is_array($value)) {
            return array_map([$this, 'sanitizeValue'], $value);
        }

        if ($value === null) {
            return '';
        }

        $value = (string) $value;
        $value = trim($value);
        $value = stripslashes($value);
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function sanitizeGlobals(): void
    {
        $globals = ['_GET', '_POST', '_REQUEST', '_COOKIE'];
        foreach ($globals as $global) {
            if (isset($GLOBALS[$global]) && is_array($GLOBALS[$global])) {
                $GLOBALS[$global] = $this->sanitizeValue($GLOBALS[$global]);
            }
        }

        // Añadir datos de php://input a una variable global personalizada
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            // Si es JSON válido
            if (is_array($data)) {
                $GLOBALS['_INPUT_DATA'] = $this->sanitizeValue($data);
            }
        } else {
            // Si no es JSON, trata los datos como una cadena de consulta
            parse_str($input, $data);
            if (is_array($data)) {
                $GLOBALS['_INPUT_DATA'] = $this->sanitizeValue($data);
            }
        }
    }
}
