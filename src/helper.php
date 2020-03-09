<?php
/**
 * Created by Reza Salarmehr
 */

/*
 * This helper is not loaded by default. If you want to use it add the following to "files" section of your composer.json
 * or use require_one to load it.
 */

if (!function_exists('intl')) {
    function cosmo(string $locale = null, array $modifiers = []): \Salarmehr\Cosmopolitan\Cosmo
    {
        return new \Salarmehr\Cosmopolitan\Cosmo($locale, $modifiers);
    }
}