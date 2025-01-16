<?php

final class Sanitizer {
    private static function sanitizeValue(mixed $value): string|array {
        if (is_array($value)) {
            return array_map([self::class, 'sanitizeValue'], $value);
        }

        if ($value === null) {
            return '';
        }

        $value = (string) $value;
        $value = trim($value);
        $value = stripslashes($value);
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public static function sanitizeGlobals(): void {
        $globals = ['_GET', '_POST', '_REQUEST', '_COOKIE'];
        foreach ($globals as $global) {
            if (isset($GLOBALS[$global]) && is_array($GLOBALS[$global])) {
                $GLOBALS[$global] = self::sanitizeValue($GLOBALS[$global]);
            }
        }
    }
}

Sanitizer::sanitizeGlobals();