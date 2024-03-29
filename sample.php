<?php // example.php
require_once 'vendor/autoload.php';

use Salarmehr\Cosmopolitan\Cosmo;


$localesTimeZones = [
    ['en_AU', 'Australia/Sydney'],
    ['en_UK', 'Europe/London'],
    ['de_DE', 'Europe/Berlin'],
    ['zh_CN', 'Asia/Chongqing'],
    ['fa-IR', 'Asia/Tehran'],
    // overwrite the numeric system to `latn`  and calendar to `buddhist`
    ['fa-IR-u-nu-latn-ca-buddhist', 'Asia/Tehran'],
    ['hi_IN', 'Asia/Jayapura'],
    ['ar_EG', 'Africa/Cairo'],
];

foreach ($localesTimeZones as [$locale, $timezone]) {
    $cosmo = new Cosmo($locale, ['timezone' => $timezone]);

    $language = $cosmo->language();
    $country = $cosmo->country();
    $flag = $cosmo->flag(); // emoji flag of the country

    echo "$flag $country - $language ($locale)" . "\n";
    echo "=================================================\n";
    echo "Language direction: " . $cosmo->direction() . "\n";

    echo $cosmo->spellout(10000000001) . "\n";
    echo $cosmo->ordinal(2) . "\n";
    echo $cosmo->quote("Quoted text!") . "\n";
    echo $cosmo->number(123400.567) . "\n";
    echo $cosmo->percentage(.14) . "\n";

    // The currency code can be passed as the second argument or passed as an item of the modifiers array
    // otherwise the currency of the region will be used
    // make sure you have exchanged the currencies if necessary before using this function.
    echo $cosmo->money(12.3) . "\n";
    echo $cosmo->currency($cosmo->modifiers['currency']) . "\n";

    // unit function is experimental
    echo $cosmo->unit('digital', 'gigabyte', 2.19) . "\n";
    echo $cosmo->unit('digital', 'gigabyte', 2.19, 'medium') . "\n";
    echo $cosmo->unit('mass', 'gram', 120) . "\n"; // default is full


    // you can send 'short','medium','long' or 'full'
    // as an argument to set the type of time or date.
    $time = new DateTime('2020/01/02 09:25:30');
    echo $cosmo->moment($time) . "\n"; // data and time
    echo $cosmo->time($time, 'full') . "\n";
    echo $cosmo->date($time, 'full') . "\n";
    echo  "\n";
}