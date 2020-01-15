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
foreach (['en_AU', 'de_DE', 'zh_CH', 'en_UK', 'fa_IR'] as $locale) {

    $intl = new \Salarmehr\Cosmopolitan($locale, 'Australia/Sydney'); // all 
    // or using the helper $intl= intl($locale);

    echo "Localising some values for: " . $intl->language($locale) . " (" . $intl->country($locale) . ")" . PHP_EOL;
    echo $intl->datetime(time()) . PHP_EOL;
    echo $intl->ordinal(2) . PHP_EOL;
    echo $intl->date(time(), Cosmopolitan::FULL) . PHP_EOL;
    echo $intl->time(time(), Cosmopolitan::SHORT) . PHP_EOL;
    echo $intl->quote("Reza!") . PHP_EOL;
    echo $intl->number(123400.567) . PHP_EOL;
    echo $intl->percentage(.14) . PHP_EOL;
    echo $intl->spellout(10000000001) . PHP_EOL;
    echo $intl->currency(12.3, 'AUD') . PHP_EOL;
    echo $intl->duration(599) . PHP_EOL . PHP_EOL;
}
~~~~~~
will output:
~~~~
Localising some values for: English (Australia)
12/1/20, 7:26:36 pm
2nd
Sunday, 12 January 2020
7:26 pm
“Reza!”
123,400.567
14%
ten billion one
$12.30
9:59

Localising some values for: Deutsch (Deutschland)
12.01.20, 19:26:36
2.
Sonntag, 12. Januar 2020
19:26
„Reza!“
123.400,567
14 %
zehn Milliarden eins
12,30 AU$
599

Localising some values for: 中文 (ZH)
2020/1/12 下午7:26:36
第2
2020年1月12日星期日
下午7:26
“Reza!”
123,400.567
14%
一百亿〇一
AU$12.30
599

Localising some values for: English (United Kingdom)
1/12/20, 7:26:36 PM
2nd
Sunday, January 12, 2020
7:26 PM
“Reza!”
123,400.567
14%
ten billion one
A$12.30
9:59

Localising some values for: فارسی (ایران)
۱۳۹۸/۱۰/۲۲،‏ ۱۹:۲۶:۳۶
۲.
۱۳۹۸ دی ۲۲, یکشنبه
۱۹:۲۶
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
