Cosmopolitan 
============
Cosmopolitan is the ultimate tool to localise your PHP application.
Just set the locale (language-country) and timezone, and your
application would be localised for your audience.

- Cosmopolitan is based on intl PHP extension and super-efficient
- Internationalisation for all countries, languages, scripts, calendars, and timezones

Features
---------
Cosmopolitan supports localisation of

- Currency name and Symbol
- Monetary ary values
- Time (from milliseconds to era)
- Numbers
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

Example
--------
The following example demonstrates a subset of available functions.
Please check the  `\src\Intl.php` to find out all available features.
~~~~~php
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
~~~~~~
will output:
~~~~
Localising some values for: English (Australia)
2nd
“Quoted text!”
123,400.567
14%
ten billion one
$12.30
Australian Dollar
9:59
23/2/20, 2:23 pm
2:23:50 pm Australian Eastern Daylight Time
Sunday, 23 February 2020

Localising some values for: English (United Kingdom)
2nd
“Quoted text!”
123,400.567
14%
ten billion one
£12.30
British Pound
9:59
2/23/20, 2:23 PM
2:23:50 PM Australian Eastern Daylight Time
Sunday, February 23, 2020

Localising some values for: Deutsch (Deutschland)
2.
„Quoted text!“
123.400,567
14 %
zehn Milliarden eins
12,30 €
Euro
599
23.02.20, 14:23
14:23:50 Ostaustralische Sommerzeit
Sonntag, 23. Februar 2020

Localising some values for: 中文 (瑞士)
第2
“Quoted text!”
123,400.567
14%
一百亿〇一
￥12.30
人民币
599
2020/2/23 下午2:23
澳大利亚东部夏令时间 下午2:23:50
2020年2月23日星期日

Localising some values for: فارسی (ایران)
۲.
«Quoted text!»
۱۲۳٬۴۰۰٫۵۶۷
۱۴٪
ده میلیارد و یک
‎ریال ۱۲
ریال ایران
۵۹۹
۱۳۹۸/۱۲/۴،‏ ۱۴:۲۳
۱۴:۲۳:۵۰ (وقت تابستانی شرق استرالیا)
۱۳۹۸ اسفند ۴, یکشنبه
~~~~

Licence
=======
MIT

Links
=====
- [Locale Explorer](http://demo.icu-project.org/icu-bin/locexp)
- [ICU Data](https://github.com/unicode-org/icu/tree/release-65-1/icu4c/source/data)
- [ICU data tables by Alexander Makarov](https://intl.rmcreative.ru/)