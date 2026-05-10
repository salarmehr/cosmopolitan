<?php
/**
 * Created by Aiden Adrian
 */
declare(strict_types=1);

namespace Salarmehr\Cosmopolitan;

use IntlChar;
use IntlDateFormatter;
use Locale;
use MessageFormatter;
use NumberFormatter;
use ResourceBundle;


enum Sentinel { case Unset; }

class Cosmo extends Locale
{
    const int NONE = IntlDateFormatter::NONE;
    const int SHORT = IntlDateFormatter::SHORT;
    const int MEDIUM = IntlDateFormatter::MEDIUM;
    const int LONG = IntlDateFormatter::LONG;
    const int FULL = IntlDateFormatter::FULL;

    const array TIME_TYPES = [
        'none' => self::NONE,
        'short' => self::SHORT,
        'medium' => self::MEDIUM,
        'long' => self::LONG,
        'full' => self::FULL,

        'n' => self::NONE,
        's' => self::SHORT,
        'm' => self::MEDIUM,
        'l' => self::LONG,
        'f' => self::FULL,
    ];

    const array UNIT_TYPES = [
        'short' => 'unitsNarrow',
        'medium' => 'unitsShort',
        'long' => 'units',
        'full' => 'units',

        's' => 'unitsNarrow',
        'm' => 'unitsShort',
        'l' => 'units',
        'f' => 'units',
    ];

    public readonly ?string $locale;

    // https://tools.ietf.org/rfc/bcp/bcp47#section-2.1
    public readonly array $subtags;

    public readonly array $modifiers;

    /**
     * @param string|null $locale BCP 47 locale identifier, e.g. en_AU. Defaults to the system locale.
     * @param array $modifiers Optional overrides: 'calendar', 'currency', 'timezone'.
     */
    public function __construct(string $locale = null, array $modifiers = [])
    {
        $this->locale = Locale::canonicalize($locale ?: Locale::getDefault());

        $subtags = Locale::parseLocale($this->locale) + ['language' => '', 'script' => '', 'region' => ''];

        $modifiers = $modifiers + [
            'calendar' => null, // when null, the common calendar of the locale will be used (Gregorian for most countries), see the moment() calendar param
            'currency' => '',
            'timezone' => null,
        ];

        if ($subtags['region'] && !$modifiers['currency']) {
            $modifiers['currency'] = new NumberFormatter($this->locale, NumberFormatter::CURRENCY)->getTextAttribute(NumberFormatter::CURRENCY_CODE);
        }

        $this->subtags = $subtags;
        $this->modifiers = $modifiers;
    }

    public static function create(string $locale = null, array $modifiers = []): Cosmo
    {
        return new self($locale, $modifiers);
    }

    /**
     * Creates a Cosmo instance from an array of locale subtags instead of a locale string.
     * @param array $subtags Locale subtag array, e.g. ['language' => 'en', 'region' => 'AU'].
     * @param array $modifiers Optional overrides: 'calendar', 'currency', 'timezone'.
     * @return Cosmo
     * @see Locale::composeLocale() for the expected array format.
     */
    public static function createFromSubtags(array $subtags, array $modifiers = []): Cosmo
    {
        return new self(Locale::composeLocale($subtags), $modifiers);
    }

    /**
     * Creates a Cosmo instance from an HTTP Accept-Language header.
     * @param string|null $header Accept-Language header value. Defaults to $_SERVER['HTTP_ACCEPT_LANGUAGE'].
     * @param array $modifiers Optional overrides: 'calendar', 'currency', 'timezone'.
     * @return Cosmo
     */
    public static function createFromHttp(?string $header = null, array $modifiers = []): Cosmo
    {
        $header = $header ?: $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null;
        return new self(Locale::acceptFromHttp($header), $modifiers);
    }


    /**
     * Retrieves a value from an ICU resource bundle, falling back to the primary language then root.
     * @param string $bundleName ICU bundle name, e.g. Bundle::LOCALE.
     * @param string ...$path One or more keys to traverse into the bundle.
     * @return ResourceBundle|int|array|string|null
     */
    public function get(string $bundleName, ...$path): ResourceBundle|int|array|string|null {
        return $this->extract($this->locale, $bundleName, $path)
            ?: $this->extract(Locale::getPrimaryLanguage($this->locale), $bundleName, $path)
                ?: $this->extract('root', $bundleName, $path);
    }

    private function extract($locale, $bundleName, array $path): ResourceBundle|int|array|string|null {
        $current = Bundle::create($locale, $bundleName, true);
        foreach ($path as $item) {
            try {
                $current = $current->get($item);
            } catch (\Throwable $exception) {
                return null;
            }
            if (!is_object($current)) {
                return $current;
            }
        }
        return $current;
    }

    #region key -> value functions

    /**
     * Returns the localised name or symbol of a currency.
     * @param string|null|Sentinel $currencyCode ISO 4217 currency code, e.g. 'AUD'. Defaults to the locale's currency.
     * @param bool $getSymbol Return the currency symbol (e.g. '$') instead of the full name.
     * @param bool $strict Throw if the currency code is invalid instead of returning it as-is.
     * @return string
     * @throws Exception If $strict is true and the currency code is not recognised.
     */
    public function currency(string|null|Sentinel $currencyCode = Sentinel::Unset, bool $getSymbol = false, bool $strict = false): string
    {
        $currencyCode = $currencyCode === Sentinel::Unset ? $this->modifiers['currency'] : (string)$currencyCode;
        $currencyCode = strtoupper($currencyCode);

        $currency = $this->get(Bundle::CURRENCY, 'Currencies', $currencyCode);

        if ($currency === null)
            if ($strict)
                throw new Exception("$currencyCode is not a valid currency code");
            else
                return $currencyCode;

        return $getSymbol ? $currency->get(0) : $currency->get(1);
    }

    /**
     * Returns the localised name of a language (e.g. 'en' -> 'English', 'glk' -> 'Gilaki').
     * If you have a full locale identifier (e.g. en_AU), pass it through Locale::getPrimaryLanguage() first.
     * @param string|null|Sentinel $language BCP 47 language code. Defaults to the instance locale.
     * @return string Empty string if the language is null or empty.
     */
    public function language(string|null|Sentinel $language = Sentinel::Unset): string
    {
        if ($language === Sentinel::Unset) $language = $this->locale;
        // if the language is null or 'getDisplayLanguage' does not work as expected and returns the current local
        if ($language === null || $language === '') return '';
        return Locale::getDisplayLanguage($language, $this->locale);
    }

    /**
     * Returns the text direction of a language: 'rtl' or 'ltr'.
     * @param string|null|Sentinel $language BCP 47 language code. Defaults to the instance locale.
     * @return string 'rtl' or 'ltr'.
     */
    public function direction(string|null|Sentinel $language = Sentinel::Unset): string
    {
        $language = $language === Sentinel::Unset ? $this->locale : (string)$language;

        try {
            $dir = Bundle::create($language, Bundle::LOCALE, true)['layout']['characters'] ?? null;
            return $dir === 'right-to-left' ? 'rtl' : 'ltr';
        } catch (\Exception $exception) {
            return 'ltr';
        }
    }

    /**
     * Translate the country of a locale (e.g. AU -> Australia)
     * @param string|Sentinel|null $country ISO 3166 country codes or a valid locale
     * @return string
     */
    public function country(string|null|Sentinel $country = Sentinel::Unset): string
    {
        if ($country === Sentinel::Unset) {
            $country = $this->subtags['region'];
        } elseif (!$country) {
            return '';
        }

        if (!preg_match('#[-_]#', $country)) {
            $country = '_' . $country;
        }
        return Locale::getDisplayRegion($country, $this->locale);
    }

    /**
     * Returns the emoji of a locale (e.g. AU -> 🇦🇺)
     * @param string|Sentinel|null $country ISO 3166 country codes or a valid locale
     * @return string
     */
    public function flag(string|null|Sentinel $country = Sentinel::Unset): string
    {
        if ($country === Sentinel::Unset) {
            $country = $this->subtags['region'];
        }

        $country = strtoupper($country ?? '');

        if (!$country) {
            return '';
        }

        // 127397 is flag offset (0x1F1E6) mines ascii offset (0x41)
        return IntlChar::chr(ord($country[0]) + 127397)
            . IntlChar::chr(ord($country[1]) + 127397);
    }

    /**
     * Returns the localised name of a script (e.g. 'Hans' -> 'Simplified Chinese').
     * If omitted, uses the script subtag from the instance locale if present.
     * @param string|null|Sentinel $script ISO 15924 script code. Defaults to the locale's script subtag.
     * @return string
     */
    public function script(string|null|Sentinel $script = Sentinel::Unset): string
    {
        if ($script === Sentinel::Unset) {
            $script = $this->subtags['script'];
        }
        $script = ucwords((string)$script);
        return (string)$this->get(Bundle::LANGUAGE, 'Scripts', $script);
    }

    /**
     * Translate the calendar identifier (e.g. "buddhist" -> "Buddhist Calendar")
     * @param string $calendar
     * @return string
     */
    public function calendar(string $calendar): string
    {
        return (string)$this->get(Bundle::LANGUAGE, 'Types', 'calendar', $calendar);
    }

    #endregion

    /**
     * Formats an ICU message string with the given arguments.
     * @param string $message ICU message pattern, e.g. '{0, plural, one {# item} other {# items}'.
     * @param array $args Arguments to substitute into the pattern.
     * @return string
     */
    public function message(string $message, array $args): string
    {
        return MessageFormatter::formatMessage($this->locale, $message, $args);
    }

    /**
     * Wraps a string in the locale's quotation marks (e.g. "text" in English, «text» in Persian).
     * @param string $quote The text to quote.
     * @return string
     */
    public function quote(string $quote): string
    {
        $delimiters = $this->get(Bundle::LOCALE, 'delimiters');
        return $delimiters->get('quotationStart') . $quote . $delimiters->get('quotationEnd');
    }

    /**
     * Formats a monetary value using the locale's currency format.
     * @param float $value The amount to format.
     * @param string|null $currency ISO 4217 currency code, e.g. 'AUD'. Defaults to the locale's currency.
     * @param string $pattern Optional NumberFormatter pattern to override the default format.
     * @param int|null $precision Number of decimal digits. Defaults to the currency's standard precision.
     * @param bool $strict Throw if no currency is available instead of returning an empty string.
     * @return string
     * @throws Exception If $strict is true and no currency code is set.
     */
    public function money(float $value, ?string $currency = null, string $pattern = '', ?int $precision = null, bool $strict = false): string
    {
        $currency = $currency ?: $this->modifiers['currency'];

        if (!$currency) {
            if ($strict)
                throw new Exception("No currency is set. Provide a currency code or set a region in the locale (e.g. en -> en_AU).");
            else
                return '';
        }

        $formatter = new NumberFormatter($this->locale, NumberFormatter::CURRENCY, $pattern);
        $formatter->setTextAttribute($formatter::CURRENCY_CODE, $currency);

        if ($precision !== null) {
            $formatter->setAttribute($formatter::FRACTION_DIGITS, $precision);
        }

        return $formatter->format($value);
    }

    /**
     * Formats a decimal value as a localised percentage (e.g. 0.2 -> '20%').
     * @param float $value Decimal value, e.g. 0.2 for 20%.
     * @param int $precision Maximum number of decimal digits.
     * @return string
     */
    public function percentage(float $value, int $precision = 3): string
    {
        $formatter = new NumberFormatter($this->locale, NumberFormatter::PERCENT);
        $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $precision);
        return $formatter->format($value);
    }

    /**
     * Formats a number using the locale's default number format.
     * @param float $number
     * @return string
     */
    public function number(float $number): string
    {
        return new NumberFormatter($this->locale, NumberFormatter::DEFAULT_STYLE)->format($number);
    }

    /**
     * Formats a number as a localised ordinal (e.g. 1 -> '1st' in English).
     * @param int $number
     * @return string
     */
    public function ordinal(int $number): string
    {
        return new NumberFormatter($this->locale, NumberFormatter::ORDINAL)->format($number);
    }

    /**
     * Returns a localised number symbol (e.g. 'decimal_separator', 'percent').
     * Accepts a NumberFormatter constant (e.g. NumberFormatter::DECIMAL_SEPARATOR_SYMBOL)
     * or a case-insensitive string name without the _SYMBOL suffix.
     * @param int|string $symbol NumberFormatter symbol constant or string name.
     * @return string
     * @throws Exception If the string name does not match a known symbol.

     */
    public function symbol(int|string $symbol): string
    {
        if (is_string($symbol)) {
            static $symbols = null;
            $symbols ??= array_filter(
                new \ReflectionClass(NumberFormatter::class)->getConstants(),
                fn($name) => str_ends_with($name, '_SYMBOL'),
                ARRAY_FILTER_USE_KEY,
            );
            $key = strtoupper($symbol) . '_SYMBOL';
            $symbol = $symbols[$key] ?? throw new Exception("$symbol is not a valid symbol name.");
        }
        return new NumberFormatter($this->locale, NumberFormatter::DECIMAL)->getSymbol($symbol);
    }


    /**
     * Spells out a number in the locale's language (e.g. 42 -> 'forty-two' in English).
     * @param float $number
     * @return string
     */
    public function spellout(float $number): string
    {
        return new NumberFormatter($this->locale, NumberFormatter::SPELLOUT)->format($number);
    }

    /**
     * @param float $duration
     * @param bool $withWords this currently works for English, for other languages it has no effect on the output
     * @return string
     */
    public function duration(float $duration, bool $withWords = false): string
    {
        $formatter = NumberFormatter::create($this->locale, NumberFormatter::DURATION);
        if ($withWords) {
            $formatter->setTextAttribute(NumberFormatter::DEFAULT_RULESET, "%with-words");
        }
        return $formatter->format($duration);
    }

    private function getTimeType(string $type): int
    {
        return self::TIME_TYPES[$type] ?? throw new Exception("$type is not a valid type for time formatting.");
    }

    /**
     * Formats a date, time, or date+time value using the locale's conventions.
     * @param mixed $value A DateTimeInterface, IntlCalendar, Unix timestamp (int/float), or localtime() array.
     * @param string $dateType Date format: 'none', 'short', 'medium', 'long', or 'full'.
     * @param string $timeType Time format: 'none', 'short', 'medium', 'long', or 'full'.
     * @param string|null $calendar Pass 'gregorian' to force the Gregorian calendar regardless of locale.
     *                              Defaults to the locale's native calendar (e.g. Persian for fa_IR).
     * @param string|null $pattern Optional ICU date/time pattern, overrides $dateType/$timeType when set.
     * @return string
     * @throws Exception If the value cannot be formatted.
     */
    public function moment(mixed $value, string $dateType = 'short', string $timeType = 'short', ?string $calendar = null, ?string $pattern = null): string
    {
        $calendar = $calendar ?? $this->modifiers['calendar'];

        $dateType = $this->getTimeType($dateType);
        $timeType = $this->getTimeType($timeType);
        $calendarType = $calendar === 'gregorian' ? IntlDateFormatter::GREGORIAN : IntlDateFormatter::TRADITIONAL;
        $pattern = $pattern ?: ''; // IntlDateFormatter does not accept null for this param

        $formatter = new IntlDateFormatter(
            locale: $this->locale,
            dateType: $dateType,
            timeType: $timeType,
            timezone: $this->modifiers['timezone'],
            calendar: $calendarType,
            pattern: $pattern,
        );
        $result = $formatter->format($value);
        if (intl_is_failure($formatter->getErrorCode())) {
            throw new Exception($formatter->getErrorMessage(), $formatter->getErrorCode());
        }
        return $result;
    }

    /**
     * Formats a date/time value using a custom ICU pattern.
     * @param mixed $value A DateTimeInterface, IntlCalendar, Unix timestamp (int/float), or localtime() array.
     * @param string $pattern ICU date/time pattern, e.g. 'YYYY-MM-dd'.
     * @param string|null $calendar Pass 'gregorian' to force the Gregorian calendar. Defaults to the locale's native calendar.
     * @return string
     * @throws Exception If the value cannot be formatted.
     */
    public function formatMoment(mixed $value, string $pattern, ?string $calendar = null): string
    {
        return $this->moment($value, 'none', 'none', $calendar, $pattern);
    }

    /**
     * Formats a date value (no time component).
     * @param mixed $value A DateTimeInterface, IntlCalendar, Unix timestamp, or localtime() array.
     * @param string $type Format type: 'none', 'short', 'medium', 'long', or 'full'.
     * @return string
     */
    public function date(mixed $value, string $type = 'short'): string
    {
        return $this->moment($value, $type, 'none');
    }

    /**
     * Formats a time value (no date component).
     * @param mixed $value A DateTimeInterface, IntlCalendar, Unix timestamp, or localtime() array.
     * @param string $type Format type: 'none', 'short', 'medium', 'long', or 'full'.
     * @return string
     */
    public function time(mixed $value, string $type = 'short'): string
    {
        return $this->moment($value, 'none', $type);
    }

    /**
     * Formats a measurement value with a localised unit (e.g. 2.19 gigabytes, 26 degrees Celsius).
     * @param string $unit Unit category, e.g. 'digital', 'temperature', 'mass'.
     * @param string $scale Unit scale within the category, e.g. 'gigabyte', 'celsius', 'gram'.
     * @param float|int $value The numeric value to format.
     * @param string $type Format width: 'short', 'medium', 'long', or 'full'.
     * @return string
     * @throws Exception If $type is not a valid format width.
     * @see https://intl.rmcreative.ru/site/unit-data?locale=en for available units and scales.
     */
    public function unit(string $unit, string $scale, float|int $value, string $type = 'full'): string
    {
        $bundle = $this->get('ICUDATA-unit', self::UNIT_TYPES[$type] ?? throw new Exception("$type is not a valid type for unit formatting."), $unit, $scale);
        $message = $this->bundleToPluralMessage($bundle);
        return MessageFormatter::formatMessage($this->locale, $message, [$value]);
    }

    private function bundleToPluralMessage(ResourceBundle $bundle): string
    {
        $categories = '';
        foreach ($bundle as $category => $string) {
            if (!is_string($string)) {
                continue;
            }
            $categories .= "$category {{$string}}";
        }
        $categories = str_replace('{0}', '#', $categories);
        return "{0,plural,$categories}";
    }
}
