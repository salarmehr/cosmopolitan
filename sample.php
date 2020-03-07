<?php // example.php
require_once 'vendor/autoload.php';

use Salarmehr\Cosmopolitan\Intl;

$time = time();

$items = [
    ['en_AU', 'Australia/Sydney'],
    ['en_GB', 'Europe/London'],
    ['de_DE', 'Europe/Berlin'],
    ['zh_CH', 'Asia/Chongqing'],
    ['fa_IR', 'Asia/Tehran'],
    ['hi_IN', 'Asia/Jayapura'],
    ['ar_EG', 'Africa/Cairo'],
];

foreach ($items as $item) {

    [$locale, $timezone] = $item;
    $intl = new Intl($locale, ['timezone' => $timezone]);

    $language = $intl->language();
    $country = $intl->country();
    $flag = $intl->flag(); // emoji flag of the country

    echo "$flag $country - $language" . "\n";

    echo $intl->spellout(10000000001) . "\n";
    echo $intl->ordinal(2) . "\n";
    echo $intl->quote("Quoted text!") . "\n";
    echo $intl->number(123400.567) . "\n";
    echo $intl->percentage(.14) . "\n";
    echo $intl->duration(599) . "\n";
    // ِ The currency code can be passed as the second argument or passed as an item of the modifiers array
    // otherwise the currency of the region will be used
    // make sure you have exchanged the currencies if necessary before using this function.
    echo $intl->money(12.3) . "\n";
    echo $intl->currency($intl->modifiers['currency']) . "\n";
    echo "Language direction: " . $intl->direction() . "\n";

    // unit function is experimental
    echo $intl->unit('digital', 'gigabyte', 2.19) . "\n";
    echo $intl->unit('digital', 'gigabyte', 2.19, 'medium') . "\n";
    echo $intl->unit('mass', 'gram', 120) . "\n"; // default is full


    // you can send 'short','medium','long' or 'full
    // as an argument to set the type of time or date.
    echo $intl->moment($time) . "\n"; // data and time
    echo $intl->time($time, 'full') . "\n";
    echo $intl->date($time, 'full') . "\n";
    echo PHP_EOL;
}