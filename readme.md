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
use Salarmehr\Cosmopolitan\Intl;

echo Intl::create('fr')->spellout(5000000); // prints: "cinq millions"
echo Intl::create('en_US')->money(11000.4,'USD'); // prints: "$11,000.40"
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

Example
--------

~~~php
<?php // example.php
require_once 'vendor/autoload.php';

use Salarmehr\Cosmopolitan\Intl;

$time = time();

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
    $intl = new Intl($locale, ['timezone' => $timezone]);

    $language = $intl->language($locale);
    $country = $intl->country($locale);
    $flag = $intl->flag($intl->subtags['region']);

    echo "$flag $country - $language" . "\n";

    echo $intl->spellout(10000000001) . "\n";
    echo $intl->ordinal(2) . "\n";
    echo $intl->quote("Quoted text!") . "\n";
    echo $intl->number(123400.567) . "\n";
    echo $intl->percentage(.14) . "\n";
    echo $intl->duration(599) . "\n";
    // ِ The currency code can be passed as the second argument or passed as an item of the modifiers array
    // otherwise the currency of the region will be used
    // make sure you have exchanged the currencies if necessary before using this function.
    echo $intl->money(12.3) . "\n";
    echo $intl->currency($intl->modifiers['currency']) . "\n";
    echo "Language direction: " . $intl->direction($locale) . "\n";

    // unit function is experimental
    echo $intl->unit('digital', 'gigabyte', 2.19) . "\n";
    echo $intl->unit('digital', 'gigabyte', 2.19, 'medium') . "\n";
    echo $intl->unit('mass', 'gram', 120) . "\n"; // default is full


    // you can send 'short','medium','long' or 'full
    // as an argument to set the type of time or date.
    echo $intl->moment($time) . "\n"; // data and time
    echo $intl->time($time, 'full') . "\n";
    echo $intl->date($time, 'full') . "\n";
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
7/3/20, 9:50 pm
9:50:33 pm Australian Eastern Daylight Time
Saturday, 7 March 2020

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
07/03/2020, 10:50
10:50:33 Greenwich Mean Time
Saturday, 7 March 2020

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
07.03.20, 11:50
11:50:33 Mitteleuropäische Normalzeit
Samstag, 7. März 2020

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
2020/3/7 下午6:50
中国标准时间 下午6:50:33
2020年3月7日星期六

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
۱۳۹۸/۱۲/۱۷،‏ ۱۴:۲۰
۱۴:۲۰:۳۳ (وقت عادی ایران)
۱۳۹۸ اسفند ۱۷, شنبه

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
7/3/20, 7:50 pm
7:50:33 pm पूर्वी इंडोनेशिया समय
शनिवार, 7 मार्च 2020

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
٧‏/٣‏/٢٠٢٠ ١٢:٥٠ م
١٢:٥٠:٣٣ م توقيت شرق أوروبا الرسمي
السبت، ٧ مارس ٢٠٢٠


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

