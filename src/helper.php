<?php
/**
 * Created by Aiden Adrian
 */

/*
 * This helper is not loaded by default. If you want to use it add the following
 * to the "files" section of your composer.json or use require_one to load it.
 */

use Salarmehr\Cosmopolitan\Cosmo;

if (!function_exists('cosmo')) {
    function cosmo(string $locale = null, array $modifiers = []): Cosmo
    {
        return new Cosmo($locale, $modifiers);
    }
}