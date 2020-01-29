<?php
/**
 * Created by Reza Salarmehr
 */

if (!function_exists('intl')) {
    function intl(string $locale = null, string $timezone = null, array $options = []): \Salarmehr\Cosmopolitan\Intl
    {
        return new \Salarmehr\Cosmopolitan\Intl($locale, $timezone, $options);
    }
}