# Cosmo

Ergonomic application localisation for PHP, powered by the `intl` extension (ICU).

Cosmo is a thin, ergonomic layer over PHP's bundled **ICU**, reached through the
[`intl`](https://www.php.net/manual/en/book.intl.php) extension. Give it a locale
(and optionally a time zone) and it formats numbers, money, dates, units, lists and
messages exactly the way your users expect. There is **no bundled locale data** —
every result comes straight from ICU and [CLDR](https://cldr.unicode.org/), covering
all languages, scripts, calendars and time zones.

Cosmo is implemented consistently across four languages — the same concepts, method
names and behaviour, each built directly on its platform's ICU:
[JavaScript](https://github.com/cosmo-intl/cosmo-js) ([docs](https://cosmo.miloun.com/?lang=js)) ·
[Python](https://github.com/cosmo-intl/cosmo-python) ([docs](https://cosmo.miloun.com/?lang=python)) ·
[Java](https://github.com/cosmo-intl/cosmo-java) ([docs](https://cosmo.miloun.com/?lang=java)) ·
**PHP**.

📖 **Full documentation, API reference and live playground:** https://cosmo.miloun.com/?lang=php

## Requirements

- PHP 8.4+ with the `intl` extension (`php -m | grep intl`)

## Install

```sh
composer require salarmehr/cosmopolitan
```

## Quick start

```php
use Miloun\Cosmo\Cosmo;

new Cosmo('es_ES')->money(11000.4, 'EUR');                    // "11.000,40 €"
new Cosmo('tr')->unit('temperature', 'celsius', 26, 'short'); // "26°C"
new Cosmo('en')->percentage(0.2);                             // "20%"
new Cosmo('fa')->language('en');                              // "انگلیسی"

// or the helper function (not autoloaded — include src/helper.php or add it
// to the "files" array in your composer.json)
cosmo('en_AU')->country();                                    // "Australia"
```

Underscore locales (`en_AU`) and [BCP-47](https://www.rfc-editor.org/info/bcp47)
[Unicode extensions](https://unicode.org/reports/tr35/#u_Extension)
(`fa-IR-u-nu-latn-ca-buddhist`) are both accepted. PHP 8.4 lets you call a method
directly on `new Cosmo(...)` without wrapping parentheses.

## What you get

- **Locale display names** — languages, regions, scripts, calendars and currencies, plus emoji flags and writing direction.
- **Numbers & money** — decimals, percentages, currencies (inferred from the region), units, compact notation, scientific, ranges, plus spelled-out and ordinal text.
- **Dates & times** — locale formats in any calendar (Gregorian, Persian, Buddhist…), custom ICU patterns, durations, date ranges, and relative times.
- **Text** — locale-aware sort and search, word/sentence/grapheme segmentation, case mapping, transliteration and quotation marks.
- **Lists** — `"A, B, and C"` conjunctions and disjunctions.
- **Messages** — [ICU MessageFormat](https://unicode-org.github.io/icu/userguide/format_parse/messages/) (`plural`, `selectordinal`, `select`).
- **Parsing & transforms** — the inverse formatters for numbers, money and dates, transliteration, UTS #39 spoof checks, and locale tag expansion.
- **Raw ICU access** — resource-bundle lookups for data the high-level methods don't cover.

See the [full API reference](https://cosmo.miloun.com/api-reference/?lang=php) for every method,
the [platform notes](https://cosmo.miloun.com/platform-notes/) for `ext-intl`'s binding
limits, and [resources](https://cosmo.miloun.com/resources/) for ICU/CLDR references.

## Development

```sh
composer install
vendor/bin/phpunit
```

## Errors

Recoverable problems throw `Cosmo\CosmoException` (extends `\Exception`), with
`InvalidArgumentException` and `UnsupportedException` subclasses — an invalid currency
in strict mode, an unsupported unit, an unknown symbol name, an unformattable date, and
the like.

## License

MIT © Aiden Adrian
