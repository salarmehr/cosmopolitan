Changelog
=======
* v3.0 — full ICU parity with cosmo-js, cosmo-python and cosmo-java; namespace rebrand

    **Breaking changes**
    - Namespace `Salarmehr\Cosmopolitan` → `Miloun\Cosmo` (find-replace on upgrade; Composer package name stays `salarmehr/cosmopolitan`)
    - Base exception `Exception` → `CosmoException` (import no longer clashes with `\Exception`)
    - Typed subclasses added: `InvalidArgumentException`, `UnsupportedException`
    - Factory methods renamed: `createFromSubtags()` → `fromSubtags()`, `createFromHttp()` → `fromAcceptLanguage()` (old names kept as `#[\Deprecated]` aliases)
    - Single-letter width aliases `s`/`m`/`l`/`f` removed — use `short`/`medium`/`long`/`full`
    - Default number rounding changed to `halfExpand` (was `halfEven`) for cross-port consistency
    - `timeZone` is now the canonical modifier key (lower-case `timezone` still accepted as alias)

    **New methods** (all ICU-backed via `ext-intl`, no bundled locale data)
    - Collation (`Collator`): `compare()`, `sort()`, `contains()` — with `numeric`/`caseFirst` tailoring
    - Segmentation (`IntlBreakIterator`): `splitWords()`, `splitSentences()`, `splitGraphemes()`, `ellipsize()`
    - Locale-aware case (`Transliterator`): `upper()`, `lower()` (e.g. Turkish dotted/dotless I)
    - Locale metadata: `pluralCategory()`, `weekInfo()`, `monthNames()`, `weekdayNames()`, `timeZoneName()`, `displayName()`, `supportedValues()`
    - Compact & ranges: `compact()`, `numberRange()`, `moneyRange()`, `dateRange()`
    - Relative time: `relativeDuration()`, `relativeDurationBetween()` (with `auto` word-forms: "yesterday")
    - Locale tag expansion: `addLikelySubtags()`, `removeLikelySubtags()`
    - Transliteration: `transliterate()`, `romanize()`
    - Spoof detection (UTS #39): `confusable()`, `suspicious()`
    - Parsing (inverse formatters): `parseNumber()`, `parseMoney()`, `parseDate()`, `parseMoment()`
    - `scientific()`, `join()` (CLDR list patterns, e.g. "A, B, and C")
    - `number()`/`percentage()`/`money()` accept `$options` array: `roundingMode`, `roundingIncrement`, `minimumFractionDigits`, `maximumFractionDigits`, `useGrouping`
    - `duration()` accepts a unit-breakdown array (`['hours' => 3, 'minutes' => 5]`)
    - `direction()` now script-based via CLDR likely-subtags (fixes minority RTL languages)

    **Other**
    - phpDocumentor API reference published to GitHub Pages
    - README rewritten to match the other three ports
* v2.0
    - Bumped minimum PHP requirement to 8.4
    - Fixed PHP 8.4 `ValueError` in `extract()` by catching `\Throwable` instead of `\Exception`
    - `customTime()` deprecated; renamed to `formatMoment()`
    - `flag()` now returns an empty string if no country is set
    - `Bundle::get()` now includes the error code when throwing exceptions
    - Replaced `func_get_args()` sentinel pattern with a PHP 8.1 unit enum (`Sentinel::Unset`)
    - `$locale`, `$subtags`, and `$modifiers` are now `readonly`
    - `money()` gains a `$strict` parameter — returns `''` by default when no currency is set, throws when `$strict = true`
    - `symbol()` now validates names via `ReflectionClass` with a static cache instead of bare `constant()` on user input
    - Typed class constants throughout (`const int`, `const array`, `const string`)
    - Removed deprecated `UNITE_TYPES` constant (use `UNIT_TYPES`)
    - Removed deprecated `customTime()` method (use `formatMoment()`)
    - Named arguments on `IntlDateFormatter` constructor for clarity
    - Added `doc/generate.php` — generates `readme.md` from `doc/readme.md` template and live output of `doc/sample.php`
* v1.2
    - Fixed PHP 8.1 deprecation notices
    - Bumped minimum PHP requirement to 8.0
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
