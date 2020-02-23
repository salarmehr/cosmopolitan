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
~~~~~~
will output:
~~~~
Localising some values for: English (Australia)
ten billion one
2nd
“Quoted text!”
123,400.567
14%
9:59
$12.30
Australian Dollar
24/2/20, 9:35 am
9:35:04 am Australian Eastern Daylight Time
Monday, 24 February 2020

Localising some values for: English (United Kingdom)
ten billion one
2nd
“Quoted text!”
123,400.567
14%
9:59
£12.30
British Pound
2/23/20, 10:35 PM
10:35:04 PM Greenwich Mean Time
Sunday, February 23, 2020

Localising some values for: Deutsch (Deutschland)
zehn Milliarden eins
2.
„Quoted text!“
123.400,567
14 %
599
12,30 €
Euro
23.02.20, 23:35
23:35:04 Mitteleuropäische Normalzeit
Sonntag, 23. Februar 2020

Localising some values for: 中文 (瑞士)
一百亿〇一
第2
“Quoted text!”
123,400.567
14%
599
￥12.30
人民币
2020/2/24 上午6:35
中国标准时间 上午6:35:04
2020年2月24日星期一

Localising some values for: فارسی (ایران)
ده میلیارد و یک
۲.
«Quoted text!»
۱۲۳٬۴۰۰٫۵۶۷
۱۴٪
۵۹۹
‎ریال ۱۲
ریال ایران
۱۳۹۸/۱۲/۵،‏ ۲:۰۵
۲:۰۵:۰۴ (وقت عادی ایران)
۱۳۹۸ اسفند ۵, دوشنبه
~~~~

Licence
=======
MIT

Links
=====
- [Locale Explorer](http://demo.icu-project.org/icu-bin/locexp)
- [ICU Data](https://github.com/unicode-org/icu/tree/release-65-1/icu4c/source/data)
- [ICU data tables by Alexander Makarov](https://intl.rmcreative.ru/)