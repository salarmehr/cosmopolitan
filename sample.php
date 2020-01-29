<?php
// example.php
require_once 'vendor/autoload.php';

use Salarmehr\Cosmopolitan\Intl;

$localAndTimezone = ['en_AU' => 'AUD', 'en_UK' => 'GBP', 'de_DE' => 'EUR', 'zh_CH' => 'CNY', 'fa_IR' => 'IRR'];
$time = time();

foreach ($localAndTimezone as $locale => $currency) {

    $intl = new Intl($locale, 'Australia/Sydney');
    // or use the helper $intl=intl($locale);

    echo "Localising some values for: " . $intl->language($locale) . " (" . $intl->country($locale) . ")" . "\n";
    echo $intl->ordinal(2) . "\n";
    echo $intl->quote("Quoted text!") . "\n";
    echo $intl->number(123400.567) . "\n";
    echo $intl->percentage(.14) . "\n";
    echo $intl->spellout(10000000001) . "\n";
    echo $intl->money(12.3, $currency) . "\n";
    echo $intl->currency($currency) . "\n";
    echo $intl->duration(599) . "\n";

    // you can send 'short','medium','long' or 'full
    // as an argument to set the type of time or date.
    echo $intl->moment($time) . "\n"; // data and time
    echo $intl->time($time, 'full') . "\n";
    echo $intl->date($time, 'full') . "\n";
    echo PHP_EOL;
}