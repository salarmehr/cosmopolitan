<?php // example.php

use Salarmehr\Cosmopolitan;

require_once '../vendor/autoload.php';

foreach (['en', 'en_UK', 'de_DE', 'zh_CH', 'fa_IR'] as $locale) {

    $intl = new \Salarmehr\Cosmopolitan\Intl($locale, 'Australia/Sydney');
    // or using the helper $intl=intl($locale);

//    $language = $intl->language(\Locale::getPrimaryLanguage($locale));
//    $country = $intl->country(\Locale::getRegion($locale));
    
    echo $intl->language('en') . "\n";
    echo $intl->country('AU') . "\n";
    echo $intl->datetime(time()) . "\n";
    echo $intl->datetime(time()) . "\n";
    echo $intl->ordinal(2) . "\n";
    echo $intl->date(time(), Cosmopolitan\Intl::FULL) . "\n";
    echo $intl->time(time(), Cosmopolitan\Intl::SHORT) . "\n";
    echo $intl->quote("Reza!") . "\n";
    echo $intl->number(123400.567) . "\n";
    echo $intl->percentage(.14) . "\n";
    echo $intl->spellout(10000000001) . "\n";
    echo $intl->currency('AUD') . "\n";
    echo $intl->money(12.3, 'AUD') . "\n";
    echo $intl->duration(599) . "\n\n";
}