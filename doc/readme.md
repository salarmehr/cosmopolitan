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
{{sample.php}}
~~~

Output:

```
{{sample.php output}}
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
