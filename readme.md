```
   ______                                       ___ __            
  / ____/___  _________ ___  ____  ____  ____  / (_) /_____ _____
 / /   / __ \/ ___/ __ `__ \/ __ \/ __ \/ __ \/ / / __/ __ `/ __ \
/ /___/ /_/ (__  ) / / / / / /_/ / /_/ / /_/ / / / /_/ /_/ / / / /
\____/\____/____/_/ /_/ /_/\____/ .___/\____/_/_/\__/\__,_/_/ /_/
                               /_/                                
```
As long as you display data, you need to present it in a format your users will understand.
Cosmopolitan is the ultimate tool to localise your PHP application.
Just set the locale (`language_COUNTRY`) and timezone, and your application is ready for your audience.

- Requires PHP 8.4+ with the `intl` extension
- Based on ICU data — covers all countries, languages, scripts, calendars, and timezones

Features
---------
* Translation of country, language, script, and calendar codes
* [ICU Messages](http://userguide.icu-project.org/formatparse/messages) (pluralisation, word gender selection, …)
* Localisation of
  - Monetary values and currency names/symbols
  - Date and time (milliseconds to the era)
  - Numbers, ordinals, and spellout
  - Percentage
  - Quoting text
  - Duration
  - Measurement units (SI and U.S.)
  - Number symbols
* Text direction (`ltr` / `rtl`) and country flag emoji

Installation
============
Ensure the `php-intl` extension is installed and enabled (`php -m | grep intl`), then run:
~~~
composer require salarmehr/cosmopolitan
~~~

Set the locale identifier (`language_COUNTRY`) and you are ready to go:
~~~php
use Salarmehr\Cosmopolitan\Cosmo;

echo Cosmo::create('en')->spellout(5_000_000);              // five million
echo Cosmo::create('es_ES')->money(11000.4);                // 11.000,40 €
echo Cosmo::create('tr')->unit('temperature', 'celsius', 26); // 26°C
~~~

Or use the helper function (not loaded by default):
~~~php
echo cosmo('en')->spellout(120); // "one hundred twenty"
~~~

Example
--------

~~~php
<?php
date_default_timezone_set('UTC');
require_once 'vendor/autoload.php';

use Salarmehr\Cosmopolitan\Cosmo;


$localesTimeZones = [
    ['en_AU', 'Australia/Sydney'],
    ['en_GB', 'Europe/London'],
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
    echo $cosmo->moment($time) . "\n"; // date and time
    echo $cosmo->time($time, 'full') . "\n";
    echo $cosmo->date($time, 'full') . "\n";
    echo  "\n";
}
~~~

Output:

```
🇦🇺 Australia - English (en_AU)
=================================================
Language direction: ltr
ten billion one
2nd
“Quoted text!”
123,400.567
14%
$12.30
Australian Dollar
2.19 gigabytes
2.19 GB
120 grams
2/1/20, 8:25 pm
8:25:30 pm Australian Eastern Daylight Time
Thursday, 2 January 2020

🇬🇧 United Kingdom - English (en_GB)
=================================================
Language direction: ltr
ten billion one
2nd
“Quoted text!”
123,400.567
14%
£12.30
British Pound
2.19 gigabytes
2.19 GB
120 grams
02/01/2020, 09:25
09:25:30 Greenwich Mean Time
Thursday, 2 January 2020

🇩🇪 Deutschland - Deutsch (de_DE)
=================================================
Language direction: ltr
zehn Milliarden eins
2.
„Quoted text!“
123.400,567
14 %
12,30 €
Euro
2,19 Gigabyte
2,19 GB
120 Gramm
02.01.20, 10:25
10:25:30 Mitteleuropäische Normalzeit
Donnerstag, 2. Januar 2020

🇨🇳 中国 - 中文 (zh_CN)
=================================================
Language direction: ltr
一百亿〇一
第2
“Quoted text!”
123,400.567
14%
¥12.30
人民币
2.19吉字节
2.19 GB
120克
2020/1/2 17:25
中国标准时间 17:25:30
2020年1月2日星期四

🇮🇷 ایران - فارسی (fa-IR)
=================================================
Language direction: rtl
ده میلیارد و یک
۲.
«Quoted text!»
۱۲۳٬۴۰۰٫۵۶۷
۱۴٪
‎ریال ۱۲
ریال ایران
۲٫۱۹ گیگابایت
۲٫۱۹ GB
۱۲۰ گرم
۱۳۹۸/۱۰/۱۲،‏ ۱۲:۵۵
۱۲:۵۵:۳۰ (وقت عادی ایران)
۱۳۹۸ دی ۱۲, پنجشنبه

🇮🇷 ایران - فارسی (fa-IR-u-nu-latn-ca-buddhist)
=================================================
Language direction: rtl
ده میلیارد و یک
2.
«Quoted text!»
123,400.567
14%
‎ریال 12
ریال ایران
2.19 گیگابایت
2.19 GB
120 گرم
2563/1/2 تقویم بودایی،‏ 12:55
12:55:30 (وقت عادی ایران)
پنجشنبه 2 ژانویهٔ 2563 تقویم بودایی

🇮🇳 भारत - हिन्दी (hi_IN)
=================================================
Language direction: ltr
दस अरब एक
2रा
“Quoted text!”
1,23,400.567
14%
₹12.30
भारतीय रुपया
2.19 गीगाबाइट
2.19 GB
120 ग्राम
2/1/20, 6:25 pm
6:25:30 pm पूर्वी इंडोनेशिया समय
गुरुवार, 2 जनवरी 2020

🇪🇬 مصر - العربية (ar_EG)
=================================================
Language direction: rtl
عشرة مليار و واحد
٢.
”Quoted text!“
١٢٣٬٤٠٠٫٥٦٧
١٤٪؜
١٢٫٣٠ ج.م.‏
جنيه مصري
٢٫١٩ غيغابايت
٢٫١٩ غ.ب
١٢٠ غرامًا
٢‏/١‏/٢٠٢٠, ١١:٢٥ ص
١١:٢٥:٣٠ ص توقيت شرق أوروبا الرسمي
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

Run through Docker
==================
You can run the example through Docker with:
~~~
docker run --rm -v $(pwd):/app -w /app php:8.4-cli php doc/sample.php
~~~
