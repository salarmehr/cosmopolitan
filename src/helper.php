<?php
/**
 * Created by Reza Salarmehr
 */

if (!function_exists('intl')) {
    function intl($locale)
    {
        return new \Salarmehr\Cosmopolitan($locale);
    }
}