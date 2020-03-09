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
- Ordinal numbers
- Quoting text
- Translating the name of languages and countries
- Spelling out of numbers
- Duration
- Units (SI and U.S.)
- Translation of countries name, languages, scripts, calendars, etc.
- [ICU Messages](http://userguide.icu-project.org/formatparse/messages) (pluralisation, word gender selection, ...)
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
echo Cosmo::create('tu')->unit('temperature','celsius',26); // 26°C - Turkish
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
    ['zh_CH', 'Asia/Chongqing'],
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

prints
~~~
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
2/1/20, 8:25 pm
8:25:30 pm Australian Eastern Daylight Time
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
02/01/2020, 09:25
09:25:30 Greenwich Mean Time
Thursday, 2 January 2020

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
2,19 Gigabytes
2,19 GB
120 Gramm
02.01.20, 10:25
10:25:30 Mitteleuropäische Normalzeit
Donnerstag, 2. Januar 2020

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
2.19吉字节
120克
2020/1/2 下午5:25
中国标准时间 下午5:25:30
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
۲٫۱۹ گیگابایت
۱۲۰ گرم
۱۳۹۸/۱۰/۱۲،‏ ۱۲:۵۵
۱۲:۵۵:۳۰ (وقت عادی ایران)
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
2/1/20, 6:25 pm
6:25:30 pm पूर्वी इंडोनेशिया समय
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
٢‏/١‏/٢٠٢٠ ١١:٢٥ ص
١١:٢٥:٣٠ ص توقيت شرق أوروبا الرسمي
الخميس، ٢ يناير ٢٠٢٠


~~~
Licence
=======
MIT

Links
=====
- [Locale Explorer](http://demo.icu-project.org/icu-bin/locexp)
- [ICU Data](https://github.com/unicode-org/icu/tree/release-65-1/icu4c/source/data)
- [ICU data tables by Alexander Makarov](https://intl.rmcreative.ru/)
- [Online ICU Message Editor](https://format-message.github.io/icu-message-format-for-translators/)

Changes
=======
* v0.5
  - Main class is renamed from Intl to Cosmo
  
* v0.4
  - Addling flag method to return the emoji flag of the locale
  - Making the input of country, language, direction, currency optional.
  
* v0.3 
  - Adding `unit` localiser method
  - Adding `direction` method to detect the direction of language (rtl or ltr)
  - Adding createFromHttp()
  - Adding createFromSubtags
  - Detecting a default currency code from locale identifier
  - Dividing options param to subtags and modifiers 

How to collaborate?
=================
 Help by creating PR or in any way you can ☺ 

