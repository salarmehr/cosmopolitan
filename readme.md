Cosmopolitan 
============
Cosmopolitan is the ultimate tool to localise your application. Just set the locale (language-country) and timezone, and your
application is localised for your audience.

- Cosmopolitan is based on intl PHP extension and super-efficient
- Internationalisation for all countries, languages, scripts, calendars, and timezones

Features
---------
Supports localisation of

- Currency
- Time (from milliseconds to era)
- Number
- Percentage
- Ordinal Numbers
- Quoting text
- Translating the name of languages and countries
- Spelling out of numbers
- Duration
- ...

Installation
============
Make sure the `php-intl` extension is installed and enabled by checking both `phpinfo()` page and  `php -m` command and run
~~~    
composer require salarmehr/cosmopolitan
~~~ 

Examples
--------
~~~~~php
<?php  // example.php

require_once 'path/to/composer/autoload.php';

foreach (['en_AU', 'en_UK', 'de_DE', 'zh_CH', 'fa_IR'] as $locale) {

    $intl = new \Salarmehr\Cosmopolitan($locale, 'Australia/Sydney');
    // or using the helper $intl=intl($locale);

    echo "Localising some values for: " . $intl->language($locale) . " (" . $intl->country($locale) . ")" . "\n";
    echo $intl->datetime(time()) . "\n";
    echo $intl->ordinal(2) . "\n";
    echo $intl->date(time(), Cosmopolitan::FULL) . "\n";
    echo $intl->time(time(), Cosmopolitan::SHORT) . "\n";
    echo $intl->quote("Reza!") . "\n";
    echo $intl->number(123400.567) . "\n";
    echo $intl->percentage(.14) . "\n";
    echo $intl->spellout(10000000001) . "\n";
    echo $intl->currency(12.3, 'AUD') . "\n";
    echo $intl->duration(599) . "\n\n";
}
~~~~~~
will output:
~~~~
Localising some values for: English (Australia)
15/1/20, 8:11:49 pm
2nd
Wednesday, 15 January 2020
8:11 pm
“Reza!”
123,400.567
14%
ten billion one
$12.30
9:59

Localising some values for: English (United Kingdom)
1/15/20, 8:11:49 PM
2nd
Wednesday, January 15, 2020
8:11 PM
“Reza!”
123,400.567
14%
ten billion one
A$12.30
9:59

Localising some values for: Deutsch (Deutschland)
15.01.20, 20:11:49
2.
Mittwoch, 15. Januar 2020
20:11
„Reza!“
123.400,567
14 %
zehn Milliarden eins
12,30 AU$
599

Localising some values for: 中文 (瑞士)
2020/1/15 下午8:11:49
第2
2020年1月15日星期三
下午8:11
“Reza!”
123,400.567
14%
一百亿〇一
AU$12.30
599

Localising some values for: فارسی (ایران)
۱۳۹۸/۱۰/۲۵،‏ ۲۰:۱۱:۴۹
۲.
۱۳۹۸ دی ۲۵, چهارشنبه
۲۰:۱۱
«Reza!»
۱۲۳٬۴۰۰٫۵۶۷
۱۴٪
ده میلیارد و یک
‎A$۱۲٫۳۰
۵۹۹

~~~~

Licence
=======
MIT
