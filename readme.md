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

Set the Locale identifier (langauge_COUNTRY) and you are ready to go
~~~php
use Salarmehr\Cosmopolitan\Intl;

echo Intl::create('fr')->spellout(5000000); // prints: "cinq millions"
echo Intl::create('en_US')->money(11000.4,'USD'); // prints: "$11,000.40"
~~~

Example
--------
The following example demonstrates a subset of available functions.
Please check the  `\src\Intl.php` to find out all available features.
~~~php
<?php
// example.php
require_once 'vendor/autoload.php';

use Salarmehr\Cosmopolitan\Intl;

$time = time();

// Locale identifier, Timezone, Currency code
// just time zone and currey are optional.
$locales = [
    ['en_AU', 'Australia/Sydney'],
    ['en_GB', 'Europe/London'],
    ['de_DE', 'Europe/Berlin'],
    ['zh_CH', 'Asia/Chongqing'],
    ['fa_IR', 'Asia/Tehran'],
    ['hi_IN', 'Asia/Jayapura'],
    ['ar_LB', 'Asia/Muscat'],
];

foreach ($locales as $locale) {

    $intl = new Intl($locale[0], ['timezone' => $locale[1]]);
    // or use the helper $intl=intl($locale[0],$local[1]);

    $language = $intl->language($locale[0]);
    $country = $intl->country($locale[0]);

    echo "Localising some values for:  $language  ($country )" . "\n";

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
    echo "Language direction: " . $intl->direction($locale[0]) . "\n";
    echo $intl->unit('digital', 'gigabyte', 2.19) . "\n";
    echo $intl->unit('digital', 'gigabyte', 2.19, 'medium') . "\n";


    // you can send 'short','medium','long' or 'full
    // as an argument to set the type of time or date.
    echo $intl->moment($time) . "\n"; // data and time
    echo $intl->time($time, 'full') . "\n";
    echo $intl->date($time, 'full') . "\n";
    echo PHP_EOL;
}
~~~
will output:
~~~
D:\server\php\php.exe D:\www\locale\sample.php
Localising some values for:  English  (Australia )
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
4/3/20, 7:45 pm
7:45:21 pm Australian Eastern Daylight Time
Wednesday, 4 March 2020

Localising some values for:  English  (United Kingdom )
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
04/03/2020, 08:45
08:45:21 Greenwich Mean Time
Wednesday, 4 March 2020

Localising some values for:  Deutsch  (Deutschland )
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
04.03.20, 09:45
09:45:21 Mitteleuropäische Normalzeit
Mittwoch, 4. März 2020

Localising some values for:  中文  (瑞士 )
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
2020/3/4 下午4:45
中国标准时间 下午4:45:21
2020年3月4日星期三

Localising some values for:  فارسی  (ایران )
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
۱۳۹۸/۱۲/۱۴،‏ ۱۲:۱۵
۱۲:۱۵:۲۱ (وقت عادی ایران)
۱۳۹۸ اسفند ۱۴, چهارشنبه

Localising some values for:  हिन्दी  (भारत )
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
4/3/20, 5:45 pm
5:45:21 pm पूर्वी इंडोनेशिया समय
बुधवार, 4 मार्च 2020

Localising some values for:  العربية  (لبنان )
عشرة مليار و واحد
٢.
”Quoted text!“
١٢٣٬٤٠٠٫٥٦٧
١٤٪؜
٥٩٩
١٢ ل.ل.‏
جنيه لبناني
Language direction: rtl
٢٫١٩ غيغابايت
٢٫١٩ غيغابايت
٤‏/٣‏/٢٠٢٠ ١٢:٤٥ م
١٢:٤٥:٢١ م توقيت الخليج
الأربعاء، ٤ آذار ٢٠٢٠
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