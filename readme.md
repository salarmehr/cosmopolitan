```
   ______                                       ___ __            
  / ____/___  _________ ___  ____  ____  ____  / (_) /_____ _____ 
 / /   / __ \/ ___/ __ `__ \/ __ \/ __ \/ __ \/ / / __/ __ `/ __ \
/ /___/ /_/ (__  ) / / / / / /_/ / /_/ / /_/ / / / /_/ /_/ / / / /
\____/\____/____/_/ /_/ /_/\____/ .___/\____/_/_/\__/\__,_/_/ /_/ 
                               /_/                                
```
Cosmopolitan is the ultimate tool to localise your PHP application.
Just set the locale (`language-country`) and timezone, and your
application is localised for your audience.

- Cosmopolitan is based on intl PHP extension and super-efficient
- Internationalisation for all countries, languages, scripts, calendars, and timezones

Features
---------
* Translation of countries name, languages, scripts, calendars, etc.
* [ICU Messages](http://userguide.icu-project.org/formatparse/messages) (pluralisation, word gender selection, ...)
* Spelling out of numbers
* Localisation of
  - Monetary values
  - Time (milliseconds to era)
  - Numbers
  - Currency name and symbol
  - Percentage
  - Ordinal numbers
  - Quoting text
  - Duration
  - Units (SI and U.S.)
  - ...

Installation
============
Make sure the `php-intl` extension is installed and enabled by checking both `phpinfo()` page and  `php -m` command and run
~~~    
composer require salarmehr/cosmopolitan
~~~ 

Set the Locale identifier (langauge_COUNTRY) and you are ready to go
~~~php
use Salarmehr\Cosmopolitan\Cosmo;
echo Cosmo::create('en')->spellout(5000000); // five million - English
echo Cosmo::create('es_ES')->money(11000.4); // 11.000,40 € - Spanish (Spain)
echo Cosmo::create('tu')->unit('temperature','celsius', 26); // 26°C - Turkish
~~~
Or you can use the helper function (it is not loaded by default). 
e.g. `echo cosmo('en')->spellout(120)` prints "one hundred twenty". 

Example
--------

~~~php
<?php // example.php
require_once 'vendor/autoload.php';

use Salarmehr\Cosmopolitan\Cosmo;


$items = [
    ['en_AU', 'Australia/Sydney'],
    ['en_GB', 'Europe/London'],
    ['de_DE', 'Europe/Berlin'],
    ['zh_CN', 'Asia/Chongqing'],
    ['fa_IR', 'Asia/Tehran'],
    ['hi_IN', 'Asia/Jayapura'],
    ['ar_EG', 'Africa/Cairo'],
];

foreach ($items as $item) {

    [$locale, $timezone] = $item;
    $cosmo = new Cosmo($locale, ['timezone' => $timezone]);

    $language = $cosmo->language();
    $country = $cosmo->country();
    $flag = $cosmo->flag(); // emoji flag of the country

    echo "$flag $country - $language" . "\n";

    echo $cosmo->spellout(10000000001) . "\n";
    echo $cosmo->ordinal(2) . "\n";
    echo $cosmo->quote("Quoted text!") . "\n";
    echo $cosmo->number(123400.567) . "\n";
    echo $cosmo->percentage(.14) . "\n";
    echo $cosmo->duration(599) . "\n";
    // ِ The currency code can be passed as the second argument or passed as an item of the modifiers array
    // otherwise the currency of the region will be used
    // make sure you have exchanged the currencies if necessary before using this function.
    echo $cosmo->money(12.3) . "\n";
    echo $cosmo->currency($cosmo->modifiers['currency']) . "\n";
    echo "Language direction: " . $cosmo->direction() . "\n";

    // unit function is experimental
    echo $cosmo->unit('digital', 'gigabyte', 2.19) . "\n";
    echo $cosmo->unit('digital', 'gigabyte', 2.19, 'medium') . "\n";
    echo $cosmo->unit('mass', 'gram', 120) . "\n"; // default is full


    // you can send 'short','medium','long' or 'full
    // as an argument to set the type of time or date.
    $time = new DateTime('2020/01/02 09:25:30');
    echo $cosmo->moment($time) . "\n"; // data and time
    echo $cosmo->time($time, 'full') . "\n";
    echo $cosmo->date($time, 'full') . "\n";
    echo PHP_EOL;
}
~~~

Output:

```
🇦🇺 Australia - English
ten billion one
2nd
“Quoted text!”
123,400.567
14%
9:59
$12.30
Australian Dollar
Language direction: ltr
2.19 gigabytes
2.19 GB
120 grams
2/1/20, 9:25 am
9:25:30 am Australian Eastern Daylight Time
Thursday, 2 January 2020

🇬🇧 United Kingdom - English
ten billion one
2nd
“Quoted text!”
123,400.567
14%
9:59
£12.30
British Pound
Language direction: ltr
2.19 gigabytes
2.19 GB
120 grams
01/01/2020, 22:25
22:25:30 Greenwich Mean Time
Wednesday, 1 January 2020

🇩🇪 Deutschland - Deutsch
zehn Milliarden eins
2.
„Quoted text!“
123.400,567
14 %
599
12,30 €
Euro
Language direction: ltr
2,19 Gigabyte
2,19 GB
120 Gramm
01.01.20, 23:25
23:25:30 Mitteleuropäische Normalzeit
Mittwoch, 1. Januar 2020

🇨🇭 瑞士 - 中文
一百亿〇一
第2
“Quoted text!”
123,400.567
14%
599
CHF 12.30
瑞士法郎
Language direction: ltr
2.19吉字节
2.19 GB
120克
2020/1/2 06:25
中国标准时间 06:25:30
2020年1月2日星期四

🇮🇷 ایران - فارسی
ده میلیارد و یک
۲.
«Quoted text!»
۱۲۳٬۴۰۰٫۵۶۷
۱۴٪
۵۹۹
‎ریال ۱۲
ریال ایران
Language direction: rtl
۲٫۱۹ گیگابایت
۲٫۱۹ <LRM>GB
۱۲۰ گرم
۱۳۹۸/۱۰/۱۲،‏ ۱:۵۵
۱:۵۵:۳۰ (وقت عادی ایران)
۱۳۹۸ دی ۱۲, پنجشنبه

🇮🇳 भारत - हिन्दी
दस अरब एक
2रा
“Quoted text!”
1,23,400.567
14%
599
₹12.30
भारतीय रुपया
Language direction: ltr
2.19 गीगाबाइट
2.19 GB
120 ग्राम
2/1/20, 7:25 am
7:25:30 am पूर्वी इंडोनेशिया समय
गुरुवार, 2 जनवरी 2020

🇪🇬 مصر - العربية
عشرة مليار و واحد
٢.
”Quoted text!“
١٢٣٬٤٠٠٫٥٦٧
١٤٪؜
٥٩٩
١٢٫٣٠ ج.م.‏
جنيه مصري
Language direction: rtl
٢٫١٩ غيغابايت
٢٫١٩ غيغابايت
١٢٠ غرامًا
٢‏/١‏/٢٠٢٠, ١٢:٢٥ ص
١٢:٢٥:٣٠ ص توقيت شرق أوروبا الرسمي
الخميس، ٢ يناير ٢٠٢٠

```

Licence
=======
MIT

Links
=====
- [ICU Documentation](https://unicode-org.github.io/icu/)
- [ICU Data](https://github.com/unicode-org/icu/tree/release-65-1/icu4c/source/data)
- [Online ICU Message Editor](https://format-message.github.io/icu-message-format-for-translators/)
- [ICU data tables by Alexander Makarov](https://intl.rmcreative.ru/)
- [The Locale Explorer by Joseph M. Newcomer](http://www.flounder.com/localeexplorer.htm)

Changelog
=======
* v1.1
  - Added `symbol()` method eg. `(new Cosmo('en'))->symbol('permill');` returns `‰`
* v0.5
  - Changed The main class name from Intl to Cosmo
* v0.4
  - Added flag method to return the emoji flag of the locale
  - Changed the input of country, language, direction, currency optional.
* v0.3 
  - Added `unit` localiser method
  - Added `direction` method to detect the direction of language (rtl or ltr)
  - Added createFromHttp()
  - Added createFromSubtags
  - Added detecting a default currency code from locale identifier
  - Changed options param to subtags and modifiers 

How to collaborate?
=================
 Help by creating PR or in any way you can ☺ 

