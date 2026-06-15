# Example

A walkthrough that formats the same set of values across several locales and time
zones. The runnable source lives in [`doc/sample.php`](sample.php).

```php
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
    // override the numeral system to `latn` and the calendar to `buddhist`
    ['fa-IR-u-nu-latn-ca-buddhist', 'Asia/Tehran'],
    ['hi_IN', 'Asia/Jayapura'],
    ['ar_EG', 'Africa/Cairo'],
];

foreach ($localesTimeZones as [$locale, $timeZone]) {
    $cosmo = new Cosmo($locale, ['timeZone' => $timeZone]);

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

    // The currency code can be passed as the second argument or via the
    // 'currency' modifier; otherwise the currency of the region is used.
    // Make sure you have converted the amount to that currency if necessary.
    echo $cosmo->money(12.3) . "\n";
    echo $cosmo->currency($cosmo->modifiers['currency']) . "\n";

    echo $cosmo->unit('digital', 'gigabyte', 2.19) . "\n";
    echo $cosmo->unit('digital', 'gigabyte', 2.19, 'medium') . "\n";
    echo $cosmo->unit('mass', 'gram', 120) . "\n"; // default width is full

    // Pass 'short', 'medium', 'long', or 'full' to set the date/time width.
    $time = new DateTime('2020/01/02 09:25:30');
    echo $cosmo->moment($time) . "\n"; // date and time
    echo $cosmo->time($time, 'full') . "\n";
    echo $cosmo->date($time, 'full') . "\n";
    echo "\n";
}
```

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
14 %
12,30 €
Euro
2,19 Gigabyte
2,19 GB
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
‎ریال ۱۲
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
‎ریال 12
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
١٢٫٣٠ ج.م.‏
جنيه مصري
٢٫١٩ غيغابايت
٢٫١٩ غ.ب
١٢٠ غرامًا
٢‏/١‏/٢٠٢٠, ١١:٢٥ ص
١١:٢٥:٣٠ ص توقيت شرق أوروبا الرسمي
الخميس، ٢ يناير ٢٠٢٠

```

You can also run this through Docker:

```sh
docker run --rm -v $(pwd):/app -w /app php:8.4-cli php doc/sample.php
```