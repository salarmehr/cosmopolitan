Super Locale 
===
Super Locale is a super efficient way to localise your application. Just set the locale of the current user. 


Features
---------
Super Locale can be used to format these categories of data for all countries, all languages and all time-zones

- Currency
- Time (from milliseconds to era)
- Number
- Percentage
- Ordinal Numbers
- Quoting text
- Translating the name of languages and countries
- Spelling out number 
- Duration
- ...

Examples
--------
~~~~~php
foreach (['en_AU', 'de', 'zh', 'en_UK', 'fa_IR'] as $locale) {

    $intl = new \Salarmehr\Intl($locale, 'Australia/Sydney');
    // or suing the helper $intl= intl($locale);

    echo "Localising some values for: " . $intl->language($locale) . " (" . $intl->country($locale) . ")" . PHP_EOL;
    echo $intl->datetime(time()) . PHP_EOL;
    echo $intl->ordinal(2) . PHP_EOL;
    echo $intl->date(time(), Intl::FULL) . PHP_EOL;
    echo $intl->time(time(), Intl::SHORT) . PHP_EOL;
    echo $intl->quote("Reza!") . PHP_EOL;
    echo $intl->number(123400.567) . PHP_EOL;
    echo $intl->percentage(.14) . PHP_EOL;
    echo $intl->spellout(10000000001) . PHP_EOL;
    echo $intl->currency(12.3, 'AUD') . PHP_EOL;
    echo $intl->duration(599) . PHP_EOL . PHP_EOL;
}
~~~~~~

Installation
============
Make sure the `php-intl` extension is installed and enabled in phpinfo() page in `phpinfo()` page and not just `php -m` command and run
~~~    
composer require salarmehr/locale
~~~ 
Licence
=======
MIT
