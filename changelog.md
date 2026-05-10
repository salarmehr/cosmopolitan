Changelog
=======
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
