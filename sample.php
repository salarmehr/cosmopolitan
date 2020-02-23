<?php
// example.php
require_once 'vendor/autoload.php';

use Salarmehr\Cosmopolitan\Intl;

$time = time();
$locales = [
    ['en_AU', 'Australia/Sydney', 'AUD'],
    ['en_UK', 'Europe/London', 'GBP'],
    ['de_DE', 'Europe/Berlin', 'EUR'],
    ['zh_CH', 'Asia/Chongqing', 'CNY'],
    ['fa_IR', 'Asia/Tehran', 'IRR'],
];

foreach ($locales as $locale) {

    $intl = new Intl($locale[0], $locale[1]);
    // or use the helper $intl=intl($locale[0],$local[1]);

    echo "Localising some values for: " . $intl->language($locale[0]) . " (" . $intl->country($locale[0]) . ")" . "\n";
    echo $intl->spellout(10000000001) . "\n";
    echo $intl->ordinal(2) . "\n";
    echo $intl->quote("Quoted text!") . "\n";
    echo $intl->number(123400.567) . "\n";
    echo $intl->percentage(.14) . "\n";
    echo $intl->duration(599) . "\n";
    echo $intl->money(12.3, $locale[2]) . "\n"; // the currency exchange is not happening in this example
    echo $intl->currency($locale[2]) . "\n";

    // you can send 'short','medium','long' or 'full
    // as an argument to set the type of time or date.
    echo $intl->moment($time) . "\n"; // data and time
    echo $intl->time($time, 'full') . "\n";
    echo $intl->date($time, 'full') . "\n";
    echo PHP_EOL;
}