<?php
/**
 * Created by Aiden Adrian
 */
declare(strict_types=1);

namespace Miloun\Cosmo;

use Collator;
use DateTimeImmutable;
use IntlBreakIterator;
use IntlCalendar;
use IntlChar;
use IntlDateFormatter;
use IntlDatePatternGenerator;
use IntlTimeZone;
use Locale;
use MessageFormatter;
use NumberFormatter;
use ResourceBundle;
use SpoofChecker;
use Transliterator;


enum Sentinel { case Unset; }

class Cosmo extends Locale
{
    const int NONE = IntlDateFormatter::NONE;
    const int SHORT = IntlDateFormatter::SHORT;
    const int MEDIUM = IntlDateFormatter::MEDIUM;
    const int LONG = IntlDateFormatter::LONG;
    const int FULL = IntlDateFormatter::FULL;

    const array TIME_WIDTHS = [
        'none' => self::NONE,
        'short' => self::SHORT,
        'medium' => self::MEDIUM,
        'long' => self::LONG,
        'full' => self::FULL,
    ];

    const array UNIT_WIDTHS = [
        'short' => 'unitsNarrow',
        'medium' => 'unitsShort',
        'long' => 'units',
        'full' => 'units',
    ];

    // Number of ICU pattern letters (M/E) per width, for month/weekday symbol names:
    // wide = 4 (MMMM), abbreviated = 3 (MMM), narrow = 5 (MMMMM). Mirrors the JS
    // WIDTH_TO_UNIT_DISPLAY mapping (none/long/full -> wide, medium -> short, short -> narrow).
    const array NAME_WIDTHS = [
        'none' => 4,
        'short' => 5,
        'medium' => 3,
        'long' => 4,
        'full' => 4,
    ];

    // cosmo-js list type -> CLDR listPattern key.
    const array LIST_TYPES = [
        'conjunction' => 'standard',
        'disjunction' => 'or',
        'unit' => 'unit',
    ];

    // width -> CLDR listPattern variant suffix (none/long/full -> wide, medium -> short, short -> narrow).
    const array LIST_WIDTHS = [
        'none' => '',
        'short' => '-narrow',
        'medium' => '-short',
        'long' => '',
        'full' => '',
    ];

    // cosmo-js timeZoneName style -> ICU date/time pattern.
    const array TZ_NAME_PATTERNS = [
        'long' => 'zzzz',
        'short' => 'z',
        'shortOffset' => 'O',
        'longOffset' => 'OOOO',
        'shortGeneric' => 'v',
        'longGeneric' => 'vvvv',
    ];

    // dateRange: CLDR intervalFormats skeleton per dateWidth. Only short/medium are
    // supported: CLDR carries no long/full interval skeletons, and synthesising them
    // (by widening the medium pattern) is unsound for many locales — see dateRange().
    const array INTERVAL_DATE_SKELETONS = [
        'short'  => 'yMd',
        'medium' => 'yMMMd',
    ];

    // dateRange: ICU calendar fields to compare, in greatest-to-smallest order.
    const array INTERVAL_DATE_FIELDS = [
        'y' => IntlCalendar::FIELD_YEAR,
        'M' => IntlCalendar::FIELD_MONTH,
        'd' => IntlCalendar::FIELD_DAY_OF_MONTH,
    ];
    const array INTERVAL_TIME_FIELDS = [
        'a' => IntlCalendar::FIELD_AM_PM,
        'H' => IntlCalendar::FIELD_HOUR_OF_DAY,
        'h' => IntlCalendar::FIELD_HOUR,
        'm' => IntlCalendar::FIELD_MINUTE,
    ];

    // NumberOptions rounding-mode name -> NumberFormatter constant (portable set;
    // every mode has a direct ICU equivalent in the JS and Python ports too).
    const array ROUNDING_MODES = [
        'ceil' => NumberFormatter::ROUND_CEILING,
        'floor' => NumberFormatter::ROUND_FLOOR,
        'expand' => NumberFormatter::ROUND_UP,
        'trunc' => NumberFormatter::ROUND_DOWN,
        'halfExpand' => NumberFormatter::ROUND_HALFUP,
        'halfTrunc' => NumberFormatter::ROUND_HALFDOWN,
        'halfEven' => NumberFormatter::ROUND_HALFEVEN,
    ];

    // Every key a NumberOptions array may carry (keys for O(1) isset() lookup). The
    // JS-only options are accepted but ignored here; anything else is a typo and throws.
    const array KNOWN_NUMBER_OPTIONS = [
        'minimumIntegerDigits' => true,
        'minimumFractionDigits' => true,
        'maximumFractionDigits' => true,
        'minimumSignificantDigits' => true,
        'maximumSignificantDigits' => true,
        'roundingMode' => true,
        'roundingIncrement' => true,
        'useGrouping' => true,
        'signDisplay' => true,
        'trailingZeroDisplay' => true,
        'roundingPriority' => true,
        'notation' => true,
        'compactDisplay' => true,
    ];

    // Every key a CollationOptions array may carry (keys for O(1) isset() lookup).
    const array KNOWN_COLLATION_OPTIONS = [
        'numeric' => true,
        'caseFirst' => true,
    ];

    // Units accepted by duration() in its multi-unit (array) form, longest first.
    const array DURATION_UNITS = ['years', 'months', 'weeks', 'days', 'hours', 'minutes', 'seconds', 'milliseconds'];

    // Normalised number-symbol name -> NumberFormatter symbol constant. Keys are
    // lower-cased with separators removed and any trailing separator/symbol/sign
    // word dropped, so 'decimal', 'group', 'minus' (the JS/Python short names) and
    // the full constant names all resolve to the same symbol.
    const array SYMBOL_NAMES = [
        'decimal' => NumberFormatter::DECIMAL_SEPARATOR_SYMBOL,
        'group' => NumberFormatter::GROUPING_SEPARATOR_SYMBOL,
        'grouping' => NumberFormatter::GROUPING_SEPARATOR_SYMBOL,
        'pattern' => NumberFormatter::PATTERN_SEPARATOR_SYMBOL,
        'percent' => NumberFormatter::PERCENT_SYMBOL,
        'permill' => NumberFormatter::PERMILL_SYMBOL,
        'permille' => NumberFormatter::PERMILL_SYMBOL,
        'minus' => NumberFormatter::MINUS_SIGN_SYMBOL,
        'plus' => NumberFormatter::PLUS_SIGN_SYMBOL,
        'exponential' => NumberFormatter::EXPONENTIAL_SYMBOL,
        'exponent' => NumberFormatter::EXPONENTIAL_SYMBOL,
        'currency' => NumberFormatter::CURRENCY_SYMBOL,
        'intlcurrency' => NumberFormatter::INTL_CURRENCY_SYMBOL,
        'monetary' => NumberFormatter::MONETARY_SEPARATOR_SYMBOL,
        'nan' => NumberFormatter::NAN_SYMBOL,
        'infinity' => NumberFormatter::INFINITY_SYMBOL,
        'significant' => NumberFormatter::SIGNIFICANT_DIGIT_SYMBOL,
        'significantdigit' => NumberFormatter::SIGNIFICANT_DIGIT_SYMBOL,
        'digit' => NumberFormatter::DIGIT_SYMBOL,
        'zero' => NumberFormatter::ZERO_DIGIT_SYMBOL,
        'zerodigit' => NumberFormatter::ZERO_DIGIT_SYMBOL,
        'pad' => NumberFormatter::PAD_ESCAPE_SYMBOL,
        'padescape' => NumberFormatter::PAD_ESCAPE_SYMBOL,
    ];

    public readonly ?string $locale;

    // https://tools.ietf.org/rfc/bcp/bcp47#section-2.1
    public readonly array $subtags;

    public readonly array $modifiers;

    /**
     * @param string|null $locale BCP 47 locale identifier, e.g. en_AU. Defaults to the system locale.
     * @param array $modifiers Optional overrides: 'calendar', 'currency', 'timeZone'.
     */
    public function __construct(string $locale = null, array $modifiers = [])
    {
        $this->locale = Locale::canonicalize($locale ?: Locale::getDefault());

        $subtags = Locale::parseLocale($this->locale) + ['language' => '', 'script' => '', 'region' => ''];

        // Accept the legacy lower-case 'timezone' key as an alias of 'timeZone'
        // (the canonical key, matching the Intl API and the cosmo-js port).
        if (isset($modifiers['timezone']) && !isset($modifiers['timeZone'])) {
            $modifiers['timeZone'] = $modifiers['timezone'];
        }

        $modifiers = $modifiers + [
            'calendar' => null, // when null, the common calendar of the locale will be used (Gregorian for most countries), see the moment() calendar param
            'currency' => '',
            'timeZone' => null,
        ];

        if ($subtags['region'] && !$modifiers['currency']) {
            $modifiers['currency'] = new NumberFormatter($this->locale, NumberFormatter::CURRENCY)->getTextAttribute(NumberFormatter::CURRENCY_CODE);
        }

        $this->subtags = $subtags;
        $this->modifiers = $modifiers;
    }

    #[\Deprecated('Use new Cosmo() directly — PHP 8.4 supports new Cosmo($locale)->method() without parentheses.')]
    public static function create(string $locale = null, array $modifiers = []): Cosmo
    {
        return new self($locale, $modifiers);
    }

    /**
     * Creates a Cosmo instance from an array of locale subtags instead of a locale string.
     * @param array $subtags Locale subtag array, e.g. ['language' => 'en', 'region' => 'AU'].
     * @param array $modifiers Optional overrides: 'calendar', 'currency', 'timeZone'.
     * @return Cosmo
     * @see Locale::composeLocale() for the expected array format.
     */
    public static function fromSubtags(array $subtags, array $modifiers = []): Cosmo
    {
        return new self(Locale::composeLocale($subtags), $modifiers);
    }

    #[\Deprecated('Use Cosmo::fromSubtags() — renamed for parity with the cosmo-js port.')]
    public static function createFromSubtags(array $subtags, array $modifiers = []): Cosmo
    {
        return self::fromSubtags($subtags, $modifiers);
    }

    /**
     * Creates a Cosmo instance from an HTTP Accept-Language header.
     * @param string|null $header Accept-Language header value. Defaults to $_SERVER['HTTP_ACCEPT_LANGUAGE'].
     * @param array $modifiers Optional overrides: 'calendar', 'currency', 'timeZone'.
     * @return Cosmo
     */
    public static function fromAcceptLanguage(?string $header = null, array $modifiers = []): Cosmo
    {
        $header = $header ?: $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null;
        return new self(Locale::acceptFromHttp($header), $modifiers);
    }

    #[\Deprecated('Use Cosmo::fromAcceptLanguage() — renamed for parity with the cosmo-js port.')]
    public static function createFromHttp(?string $header = null, array $modifiers = []): Cosmo
    {
        return self::fromAcceptLanguage($header, $modifiers);
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
     * @param bool $getSymbol Return the standard currency symbol (e.g. 'A$') instead of the full name.
     *                        This is the disambiguated symbol — matches the cosmo-js port's
     *                        currencyDisplay:"symbol" (not the ambiguous narrow form).
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
                throw new InvalidArgumentException("$currencyCode is not a valid currency code");
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
            // Direction is a property of the script, not the locale: the per-locale
            // `layout/characters` bundle field is only populated for major locales,
            // so minority RTL languages (Dhivehi/Thaana, N'Ko) silently inherited
            // root's left-to-right. An explicit script subtag (ar-Latn) wins;
            // otherwise resolve the likely script and ask ICU for its direction.
            $script = Locale::getScript($language) ?: (self::maximiseSubtags($language)['script'] ?? '');
            return $script !== '' && self::isRtlScript($script) ? 'rtl' : 'ltr';
        } catch (\Exception $exception) {
            return 'ltr';
        }
    }

    /**
     * Returns a new Cosmo with likely subtags added (e.g. "en" → "en_Latn_US").
     * ext-intl wraps neither uloc_addLikelySubtags nor Intl.Locale#maximize, so this
     * implements the CLDR likely-subtags algorithm over ICU's own likelySubtags table.
     */
    public function addLikelySubtags(): static
    {
        $core = self::maximiseSubtags($this->locale);
        return $core === null
            ? new static($this->locale, $this->modifiers)
            : new static(self::replaceCoreSubtags($this->locale, $core), $this->modifiers);
    }

    /**
     * Returns a new Cosmo with likely subtags removed (e.g. "en_Latn_US" → "en").
     * CLDR algorithm: keep the shortest of language / language_region /
     * language_script that still maximises to the same locale.
     */
    public function removeLikelySubtags(): static
    {
        $max = self::maximiseSubtags($this->locale);
        if ($max === null) {
            return new static($this->locale, $this->modifiers);
        }
        $result = $max;
        $trials = [
            ['language' => $max['language'], 'script' => '', 'region' => ''],
            ['language' => $max['language'], 'script' => '', 'region' => $max['region']],
            ['language' => $max['language'], 'script' => $max['script'], 'region' => ''],
        ];
        foreach ($trials as $trial) {
            if (self::maximiseSubtags(Locale::composeLocale(array_filter($trial))) == $max) {
                $result = $trial;
                break;
            }
        }
        return new static(self::replaceCoreSubtags($this->locale, $result), $this->modifiers);
    }

    /**
     * Maximised ['language', 'script', 'region'] for a locale per the CLDR
     * likely-subtags algorithm (UTS #35), resolved against ICU's likelySubtags
     * table, or null when nothing matches. Subtags present in the source always
     * win; only missing ones are filled from the table (so az → az_Latn_AZ but
     * az_IQ → az_Arab_IQ, and an explicit script like ar_Latn is never replaced).
     */
    private static function maximiseSubtags(string $locale): ?array
    {
        $bundle = ResourceBundle::create('likelySubtags', 'ICUDATA', false);
        if ($bundle === null) {
            return null;
        }
        // parseLocale('') returns the *environment default* locale's subtags;
        // treat an empty ID as und explicitly so results don't depend on the host.
        $sub = $locale === '' ? [] : (Locale::parseLocale($locale) ?: []);
        $lang = ($sub['language'] ?? '') ?: 'und';
        $script = $sub['script'] ?? '';
        $region = $sub['region'] ?? '';
        // UTS #35 lookup order, skipping keys with missing components.
        $match = ($script !== '' && $region !== '' ? $bundle->get("{$lang}_{$script}_{$region}") : null)
            ?? ($region !== '' ? $bundle->get("{$lang}_{$region}") : null)
            ?? ($script !== '' ? $bundle->get("{$lang}_{$script}") : null)
            ?? $bundle->get($lang)
            ?? ($script !== '' ? $bundle->get("und_{$script}") : null);
        if ($match === null) {
            return null;
        }
        $m = Locale::parseLocale($match) + ['language' => '', 'script' => '', 'region' => ''];
        return [
            'language' => $lang !== 'und' ? $lang : $m['language'],
            'script' => $script !== '' ? $script : $m['script'],
            'region' => $region !== '' ? $region : $m['region'],
        ];
    }

    /**
     * Rebuilds a locale ID with new core language/script/region subtags while
     * preserving its variants and any @keyword extensions, which parseLocale/
     * composeLocale would otherwise drop.
     */
    private static function replaceCoreSubtags(string $locale, array $core): string
    {
        $at = strpos($locale, '@');
        $base = $at === false ? $locale : substr($locale, 0, $at);
        $extensions = $at === false ? '' : substr($locale, $at);
        // parseLocale('') would inject the environment default locale's subtags.
        $sub = $base === '' ? [] : (Locale::parseLocale($base) ?: []);
        unset($sub['language'], $sub['script'], $sub['region']);
        return Locale::composeLocale(array_filter($core, fn($v) => $v !== '') + $sub) . $extensions;
    }

    /**
     * Whether an ISO 15924 script is written right-to-left, derived live from ICU's
     * Unicode property data: the bidi class of the script's first strong character.
     * (ext-intl exposes no Script::isRightToLeft(), unlike PyICU and Intl.Locale;
     * composite scripts with no direct codepoints, e.g. Jpan, resolve to LTR.)
     */
    private static function isRtlScript(string $script): bool
    {
        static $cache = [];
        if (isset($cache[$script])) {
            return $cache[$script];
        }
        $rtl = false;
        $enum = IntlChar::getPropertyValueEnum(IntlChar::PROPERTY_SCRIPT, $script);
        if ($enum >= 0) {
            for ($cp = 0; $cp <= IntlChar::CODEPOINT_MAX; $cp++) {
                if (IntlChar::getIntPropertyValue($cp, IntlChar::PROPERTY_SCRIPT) !== $enum) {
                    continue;
                }
                $direction = IntlChar::charDirection($cp);
                if ($direction === IntlChar::CHAR_DIRECTION_LEFT_TO_RIGHT) {
                    break;
                }
                if ($direction === IntlChar::CHAR_DIRECTION_RIGHT_TO_LEFT
                    || $direction === IntlChar::CHAR_DIRECTION_RIGHT_TO_LEFT_ARABIC) {
                    $rtl = true;
                    break;
                }
                // Neutral / number / mark codepoint: keep scanning for a strong one.
            }
        }
        return $cache[$script] = $rtl;
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

        // Only a bare two-letter region code maps to a flag; anything else
        // (empty, too short/long, non-alphabetic) has no flag.
        if (!preg_match('/^[A-Z]{2}$/', $country)) {
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
        // ISO 15924 codes are Titlecase (e.g. 'Latn'). ucwords() only upper-cases
        // the first letter and leaves the rest, so 'lATN' would stay 'LATN' and miss
        // the CLDR key; normalise the whole code instead.
        $script = ucfirst(strtolower((string)$script));
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
     * @param string $pattern ICU message pattern, e.g. '{0, plural, one {# item} other {# items}'.
     * @param array $args Arguments to substitute into the pattern.
     * @return string
     */
    public function message(string $pattern, array $args = []): string
    {
        return MessageFormatter::formatMessage($this->locale, $pattern, $args);
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
     *
     * The shared parameters ($value, $currency, $precision, $strict) line up with
     * the cosmo-js port's money(value, code, {precision, strict}); $pattern
     * is a PHP-only extra (the Intl API exposes no NumberFormatter pattern) and is
     * therefore last.
     * @param float $value The amount to format.
     * @param string|null $currency ISO 4217 currency code, e.g. 'AUD'. Defaults to the locale's currency.
     * @param int|null $precision Number of decimal digits. Defaults to the currency's standard precision.
     * @param bool $strict Throw if no currency is available instead of returning an empty string.
     * @param string $pattern Optional NumberFormatter pattern to override the default format (PHP-only).
     * @return string
     * @throws Exception If $strict is true and no currency code is set.
     */
    public function money(float $value, ?string $currency = null, ?int $precision = null, bool $strict = false, string $pattern = '', array $options = []): string
    {
        $currency = $currency ?: $this->modifiers['currency'];

        if (!$currency) {
            if ($strict)
                throw new InvalidArgumentException("No currency is set. Provide a currency code or set a region in the locale (e.g. en -> en_AU).");
            else
                return '';
        }

        // A malformed code makes ICU silently fall back or mangle the output;
        // reject it up front so every port raises the same branded error.
        $currency = strtoupper($currency);
        if (!preg_match('/^[A-Z]{3}$/', $currency)) {
            throw new InvalidArgumentException("\"$currency\" is not a valid currency code.");
        }

        $formatter = new NumberFormatter($this->locale, NumberFormatter::CURRENCY, $pattern);
        $formatter->setTextAttribute($formatter::CURRENCY_CODE, $currency);

        if ($precision !== null) {
            $formatter->setAttribute($formatter::FRACTION_DIGITS, $precision);
        }
        $this->applyNumberOptions($formatter, $options);

        return $formatter->format($value);
    }

    /**
     * Formats a decimal value as a localised percentage (e.g. 0.2 -> '20%').
     * @param float $value Decimal value, e.g. 0.2 for 20%.
     * @param int $precision Maximum number of decimal digits.
     * @return string
     */
    public function percentage(float $value, int $precision = 3, array $options = []): string
    {
        $formatter = new NumberFormatter($this->locale, NumberFormatter::PERCENT);
        $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $precision);
        $this->applyNumberOptions($formatter, $options);
        return $formatter->format($value);
    }

    /**
     * Formats a number using the locale's default number format.
     * @param float $number
     * @param array $options Optional number-formatting controls: 'minimumIntegerDigits',
     *      'minimumFractionDigits', 'maximumFractionDigits', 'minimumSignificantDigits',
     *      'maximumSignificantDigits', 'roundingMode'
     *      (ceil/floor/expand/trunc/halfExpand/halfTrunc/halfEven), 'roundingIncrement',
     *      'useGrouping'. Portable across the JS and Python ports. The JS-only options
     *      ('signDisplay', 'trailingZeroDisplay', 'roundingPriority', 'notation',
     *      'compactDisplay') have no equivalent in ICU's NumberFormatter and are ignored.
     * @return string
     */
    public function number(float $number, array $options = []): string
    {
        $formatter = new NumberFormatter($this->locale, NumberFormatter::DEFAULT_STYLE);
        $this->applyNumberOptions($formatter, $options);
        return $formatter->format($number);
    }

    /**
     * Formats a number with a fixed number of fraction digits — always exactly
     * $fractionDigits, padding with trailing zeros and rounding as needed. Use it
     * when you want 1 to render as '1.00' and 1.002 to stay '1.00', never '1.0'.
     * Pass an $options bag (see
     * {@see number()}) to widen the band — e.g. ['maximumFractionDigits' => 3] —
     * or tweak rounding/grouping.
     * @param float $value
     * @param int $fractionDigits Fixed fraction digits (sets both the min and the max); defaults to 2.
     * @param array $options Optional number-formatting controls.
     * @return string
     */
    public function precision(float $value, int $fractionDigits = 2, array $options = []): string
    {
        $formatter = new NumberFormatter($this->locale, NumberFormatter::DECIMAL);
        $formatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, $fractionDigits);
        $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $fractionDigits);
        $this->applyNumberOptions($formatter, $options);
        return $formatter->format($value);
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
     * Returns a localised number symbol (e.g. 'decimal', 'group', 'percent').
     * Accepts a NumberFormatter symbol constant, or a case-insensitive name. The
     * short names match the JS and Python ports ('decimal', 'group', 'minus',
     * 'plus', …); the full constant names ('decimal_separator', 'minus_sign', …)
     * and the `_symbol`/`_separator`/`_sign` suffixes are accepted too.
     * @param int|string $symbol NumberFormatter symbol constant or string name.
     * @return string
     * @throws Exception If the string name does not match a known symbol.
     */
    public function symbol(int|string $symbol): string
    {
        if (is_string($symbol)) {
            // First try the curated short names the JS/Python ports use ('decimal',
            // 'group', 'minus', …): lower-case, strip separators, then drop a trailing
            // separator/symbol/sign word so 'decimal', 'decimal_separator' and
            // 'decimalSeparator' all collapse to the same key.
            $key = preg_replace('/[_\s-]/', '', strtolower($symbol));
            $key = preg_replace('/(separator|symbol|sign)$/', '', $key);
            if (isset(self::SYMBOL_NAMES[$key])) {
                $symbol = self::SYMBOL_NAMES[$key];
            } else {
                // Fall back to any NumberFormatter <NAME>_SYMBOL constant by full
                // name (e.g. 'monetary_grouping_separator').
                static $constants = null;
                $constants ??= array_filter(
                    (new \ReflectionClass(NumberFormatter::class))->getConstants(),
                    fn($name) => str_ends_with($name, '_SYMBOL'),
                    ARRAY_FILTER_USE_KEY,
                );
                $symbol = $constants[strtoupper($symbol) . '_SYMBOL']
                    ?? throw new InvalidArgumentException("$symbol is not a valid symbol name.");
            }
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
     * Formats an undirected duration.
     * @param float|array $duration A number of seconds (rendered as the "339:17:20"
     *      clock form), or an array of units — ['hours' => 3, 'minutes' => 5] — for
     *      multi-unit output ("3 hours, 5 minutes"). Accepted units: years, months,
     *      weeks, days, hours, minutes, seconds, milliseconds.
     * @param bool $withWords Scalar form → spell the units out (English only);
     *      array form → wide units (vs the abbreviated default).
     * @return string
     */
    public function duration(float|array $duration, bool $withWords = false): string
    {
        if (is_array($duration)) {
            return $this->durationParts($duration, $withWords);
        }
        $formatter = NumberFormatter::create($this->locale, NumberFormatter::DURATION);
        if ($withWords) {
            $formatter->setTextAttribute(NumberFormatter::DEFAULT_RULESET, "%with-words");
        }
        return $formatter->format($duration);
    }

    /**
     * Renders a multi-unit duration by formatting each unit with unit() and joining
     * them with the locale's CLDR unit-list pattern (PHP's intl has no MeasureFormat).
     */
    private function durationParts(array $parts, bool $withWords): string
    {
        $width = $withWords ? 'long' : 'medium';
        $pieces = [];
        foreach (self::DURATION_UNITS as $unit) {
            if (!empty($parts[$unit])) {
                $pieces[] = $this->unit('duration', substr($unit, 0, -1), $parts[$unit], $width);
            }
        }
        return $pieces ? $this->join($pieces, 'unit', $width) : '';
    }

    private function getWidth(string $width): int
    {
        return self::TIME_WIDTHS[$width] ?? throw new InvalidArgumentException("$width is not a valid width for date/time formatting.");
    }

    /**
     * Formats a date, time, or date+time value using the locale's conventions.
     * @param mixed $value A DateTimeInterface, IntlCalendar, Unix timestamp (int/float), or localtime() array.
     * @param string $dateWidth Date format: 'none', 'short', 'medium', 'long', or 'full'.
     * @param string $timeWidth Time format: 'none', 'short', 'medium', 'long', or 'full'.
     * @param string|null $calendar Pass 'gregorian' to force the Gregorian calendar regardless of locale.
     *                              Defaults to the locale's native calendar (e.g. Persian for fa_IR).
     * @param string|null $pattern Optional ICU date/time pattern, overrides $dateWidth/$timeWidth when set.
     * @return string
     * @throws Exception If the value cannot be formatted.
     */
    public function moment(mixed $value, string $dateWidth = 'short', string $timeWidth = 'short', ?string $calendar = null, ?string $pattern = null): string
    {
        $calendar = $calendar ?? $this->modifiers['calendar'];

        $dateType = $this->getWidth($dateWidth);
        $timeType = $this->getWidth($timeWidth);
        $calendarType = $calendar === 'gregorian' ? IntlDateFormatter::GREGORIAN : IntlDateFormatter::TRADITIONAL;
        $pattern = $pattern ?: ''; // IntlDateFormatter does not accept null for this param

        $formatter = new IntlDateFormatter(
            locale: $this->locale,
            dateType: $dateType,
            timeType: $timeType,
            timezone: $this->modifiers['timeZone'],
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
     * Parses a localised number ('1.234,56' in de -> 1234.56). The inverse of number().
     * @throws Exception If the text is not a number in this locale.
     */
    public function parseNumber(string $text): float
    {
        $value = new NumberFormatter($this->locale, NumberFormatter::DECIMAL)->parse($text);
        if ($value === false) {
            throw new InvalidArgumentException("\"$text\" cannot be parsed as a number in $this->locale.");
        }
        return (float)$value;
    }

    /**
     * Parses a localised monetary string ('$12.30' -> amount 12.3, currency USD).
     * The inverse of money().
     * @return array{amount: float, currency: string}
     * @throws Exception If the text is not a monetary value in this locale.
     */
    public function parseMoney(string $text): array
    {
        $currency = '';
        $amount = new NumberFormatter($this->locale, NumberFormatter::CURRENCY)->parseCurrency($text, $currency);
        if ($amount === false) {
            throw new InvalidArgumentException("\"$text\" cannot be parsed as money in $this->locale.");
        }
        return ['amount' => $amount, 'currency' => $currency];
    }

    /**
     * Parses a localised date written at the given width. The inverse of date().
     * @param string $width 'short', 'medium', 'long', or 'full'.
     * @return DateTimeImmutable The parsed moment (UTC).
     * @throws Exception If the text does not match the locale's date format.
     */
    public function parseDate(string $text, string $width = 'short'): DateTimeImmutable
    {
        if ($width === 'none') {
            throw new InvalidArgumentException("none is not a valid parse width.");
        }
        $formatter = new IntlDateFormatter(
            locale: $this->locale,
            dateType: $this->getWidth($width),
            timeType: IntlDateFormatter::NONE,
            timezone: $this->modifiers['timeZone'],
            calendar: IntlDateFormatter::TRADITIONAL,
        );
        $timestamp = $formatter->parse($text);
        if ($timestamp === false) {
            throw new InvalidArgumentException("\"$text\" cannot be parsed as a date in $this->locale.");
        }
        return DateTimeImmutable::createFromTimestamp($timestamp);
    }

    /**
     * Parses a moment with a raw ICU pattern. The inverse of formatMoment().
     * @return DateTimeImmutable The parsed moment (UTC).
     * @throws Exception If the text does not match the pattern.
     */
    public function parseMoment(string $text, string $pattern): DateTimeImmutable
    {
        $formatter = new IntlDateFormatter(
            locale: $this->locale,
            dateType: IntlDateFormatter::NONE,
            timeType: IntlDateFormatter::NONE,
            timezone: $this->modifiers['timeZone'],
            calendar: IntlDateFormatter::TRADITIONAL,
            pattern: $pattern,
        );
        $timestamp = $formatter->parse($text);
        if ($timestamp === false) {
            throw new InvalidArgumentException("\"$text\" does not match the pattern \"$pattern\".");
        }
        return DateTimeImmutable::createFromTimestamp($timestamp);
    }

    /**
     * Formats a date value (no time component).
     * @param mixed $value A DateTimeInterface, IntlCalendar, Unix timestamp, or localtime() array.
     * @param string $width Format width: 'none', 'short', 'medium', 'long', or 'full'.
     * @return string
     */
    public function date(mixed $value, string $width = 'short'): string
    {
        return $this->moment($value, $width, 'none');
    }

    /**
     * Formats a time value (no date component).
     * @param mixed $value A DateTimeInterface, IntlCalendar, Unix timestamp, or localtime() array.
     * @param string $width Format width: 'none', 'short', 'medium', 'long', or 'full'.
     * @return string
     */
    public function time(mixed $value, string $width = 'short'): string
    {
        return $this->moment($value, 'none', $width);
    }

    /**
     * Formats a date or time range using CLDR interval format patterns (e.g. "Feb 2 – 5, 2020").
     * Elements shared by both endpoints (year, month) are elided.
     *
     * Backed by ICU's CLDR `intervalFormats` resource — no hardcoded locale data. PHP's
     * intl extension does not bind ICU's `DateIntervalFormat`, so this reads the interval
     * pattern for the greatest-differing field from the ResourceBundle, splits it where
     * the repeated field recurs, and formats each half with IntlDateFormatter.
     *
     * Only the 'short' and 'medium' widths are supported: CLDR carries no long/full
     * interval skeletons, and ICU's own derivation of them is unavailable through
     * ext-intl. Supported combinations:
     *   - Date-only: (short|medium, 'none')  → e.g. "Feb 2 – 5, 2020"
     *   - Time-only: ('none', short|medium)  → e.g. "3:00 – 5:30 PM"
     * When a locale's interval table lacks the differing field, the two endpoints are
     * joined with the locale's range separator without elision (still correct output).
     *
     * @param mixed  $start     Start of the range (DateTimeInterface, IntlCalendar, Unix timestamp, or localtime() array).
     * @param mixed  $end       End of the range.
     * @param string $dateWidth Date component width: 'none', 'short', or 'medium'.
     * @param string $timeWidth Time component width: 'none', 'short', or 'medium'.
     * @return string
     * @throws Exception If $dateWidth or $timeWidth is 'long'/'full' or otherwise invalid.
     */
    public function dateRange(mixed $start, mixed $end, string $dateWidth = 'medium', string $timeWidth = 'none'): string
    {
        foreach (['dateWidth' => $dateWidth, 'timeWidth' => $timeWidth] as $name => $w) {
            if (!in_array($w, ['none', 'short', 'medium'], true)) {
                throw new InvalidArgumentException("dateRange supports only 'none', 'short', or 'medium' widths (got $name='$w'); long/full interval formatting is not available through PHP's intl extension.");
            }
        }

        $isGregorian  = ($this->modifiers['calendar'] === 'gregorian');
        $calendarType = $isGregorian ? IntlDateFormatter::GREGORIAN : IntlDateFormatter::TRADITIONAL;
        $tz           = $this->modifiers['timeZone'];
        $fmt          = fn(string $p) => new IntlDateFormatter($this->locale, IntlDateFormatter::NONE, IntlDateFormatter::NONE, $tz, $calendarType, $p);

        // The locale's CLDR interval-format patterns (native calendar unless gregorian is forced).
        $rb           = ResourceBundle::create($this->locale, null, true);
        $calendarName = $isGregorian ? 'gregorian' : ($rb['calendar']['default'] ?? 'gregorian');
        $ivf          = $rb['calendar'][$calendarName]['intervalFormats'];

        // The locale's range separator, taken from the "{0} – {1}" fallback pattern.
        preg_match('/^\{0\}(.*)\{1\}$/u', $ivf['fallback'] ?? '{0} – {1}', $m);
        $separator = $m[1] ?? ' – ';

        // Choose the interval skeleton and the calendar fields to compare, per component.
        $gen = IntlDatePatternGenerator::create($this->locale);
        if ($timeWidth === 'none' && $dateWidth !== 'none') {
            $skeleton = self::INTERVAL_DATE_SKELETONS[$dateWidth];
            $fields   = self::INTERVAL_DATE_FIELDS;
        } elseif ($dateWidth === 'none' && $timeWidth !== 'none') {
            $hour     = str_contains($gen->getBestPattern('j'), 'H') ? 'H' : 'h';
            $skeleton = ['short' => $hour . 'm', 'medium' => $hour . 'ms'][$timeWidth];
            $fields   = self::INTERVAL_TIME_FIELDS;
        } else {
            // Combined date+time (or both 'none'): CLDR has no interval pattern, so join
            // two full moments with the separator (no elision).
            return $this->moment($start, $dateWidth, $timeWidth) . $separator . $this->moment($end, $dateWidth, $timeWidth);
        }

        // The single-endpoint pattern for this skeleton, used when there is nothing to
        // elide. ICU renders interval endpoints from the skeleton, not the locale's plain
        // date/time style, so e.g. de medium is "2. Feb. 2020", not the numeric "02.02.2020".
        $single = $gen->getBestPattern($skeleton);

        // Find the greatest calendar field that differs between the two endpoints; its
        // CLDR interval pattern (e.g. "MMM d – d, y") repeats that field once.
        $calStart = IntlCalendar::createInstance($tz ?? date_default_timezone_get(), $this->locale);
        $calEnd   = clone $calStart;
        $calStart->setTime(self::toTimestampMs($start));
        $calEnd->setTime(self::toTimestampMs($end));

        $skeletonRb = $ivf->get($skeleton);
        $pattern    = null;
        $anyDiff    = false;
        foreach ($fields as $letter => $field) {
            if ($calStart->get($field) !== $calEnd->get($field)) {
                $anyDiff = true;
                if (($p = $skeletonRb?->get($letter)) !== null) { $pattern = $p; break; }
            }
        }

        if ($pattern === null) {
            // No field differs → endpoints render identically (format the start alone). A
            // field differs but the table lacks it → unelided join of two endpoints.
            return $anyDiff
                ? $fmt($single)->format($start) . $separator . $fmt($single)->format($end)
                : $fmt($single)->format($start);
        }

        // Split the interval pattern where the repeated field recurs: the first half
        // (with the separator) renders the start, the second half renders the end.
        [$first, $second] = self::splitIntervalPattern($pattern);
        return $fmt($first)->format($start) . $fmt($second)->format($end);
    }

    /**
     * Formats a measurement value with a localised unit (e.g. 2.19 gigabytes, 26 degrees Celsius).
     * @param string $category Unit category, e.g. 'digital', 'temperature', 'mass'.
     * @param string $unit Unit within the category, e.g. 'gigabyte', 'celsius', 'gram'.
     * @param float|int $value The numeric value to format.
     * @param string $width Format width: 'short', 'medium', 'long', or 'full'.
     * @return string
     * @throws Exception If $width is not a valid format width.
     * @see https://intl.rmcreative.ru/site/unit-data?locale=en for available units and scales.
     */
    public function unit(string $category, string $unit, float|int $value, string $width = 'full'): string
    {
        $bundle = $this->get('ICUDATA-unit', self::UNIT_WIDTHS[$width] ?? throw new InvalidArgumentException("$width is not a valid width for unit formatting."), $category, $unit);
        if (!$bundle instanceof ResourceBundle) {
            throw new InvalidArgumentException("$unit is not a unit supported by ICU.");
        }
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

    /**
     * These methods mirror the cosmo-js port. Each is backed by the
     * runtime's ICU (Collator, IntlBreakIterator, IntlCalendar, Transliterator,
     * NumberFormatter, IntlDateFormatter, CLDR resource bundles) — no hardcoded
     * locale data. JS-only methods that depend on Intl facilities PHP's `intl`
     * extension does not expose (addLikelySubtags/removeLikelySubtags,
     * bestMatch, indexBuckets, personName) are intentionally absent.
     */
    #region cosmo-js parity

    /**
     * Locale-aware comparison of two strings, suitable for usort().
     * @return int Negative, 0, or positive.
     */
    public function compare(string $a, string $b, array $options = []): int
    {
        $collator = new Collator($this->locale);
        $this->applyCollationOptions($collator, $options);
        return (int)$collator->compare($a, $b);
    }

    /**
     * Returns a new array sorted by the locale's collation rules.
     * @param iterable $items The items to sort.
     * @param callable|null $key Optional accessor returning the string to sort each item by.
     * @return array
     */
    public function sort(iterable $items, ?callable $key = null, array $options = []): array
    {
        $array = is_array($items) ? array_values($items) : iterator_to_array($items, false);
        $collator = new Collator($this->locale);
        $this->applyCollationOptions($collator, $options);
        $key ??= static fn($item): string => (string)$item;
        usort($array, fn($a, $b) => $collator->compare($key($a), $key($b)));
        return $array;
    }

    /**
     * Locale-aware substring test that honours the locale's collation, so
     * accents/case can be ignored. Compares grapheme windows with a Collator
     * (PHP's intl exposes no ICU StringSearch).
     * @param string $sensitivity 'base' (ignore case & accents, default), 'accent',
     *                            'case', or 'variant' (exact).
     * @throws Exception If $sensitivity is not recognised.
     */
    public function contains(string $haystack, string $needle, string $sensitivity = 'base', array $options = []): bool
    {
        if ($needle === '') return true;

        $collator = new Collator($this->locale);
        $this->applyCollationOptions($collator, $options);
        switch ($sensitivity) {
            case 'base':
                $collator->setStrength(Collator::PRIMARY);
                break;
            case 'accent':
                $collator->setStrength(Collator::SECONDARY);
                break;
            case 'case':
                $collator->setStrength(Collator::PRIMARY);
                $collator->setAttribute(Collator::CASE_LEVEL, Collator::ON);
                break;
            case 'variant':
                $collator->setStrength(Collator::TERTIARY);
                break;
            default:
                throw new InvalidArgumentException("$sensitivity is not a valid sensitivity (base, accent, case, variant).");
        }

        $hayLength = grapheme_strlen($haystack);
        $needleLength = grapheme_strlen($needle);
        for ($i = 0; $i + $needleLength <= $hayLength; $i++) {
            if ($collator->compare(grapheme_substr($haystack, $i, $needleLength), $needle) === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Splits text into words using the locale's word-boundary rules, keeping only
     * word-like segments (drops whitespace and punctuation).
     * @return string[]
     */
    public function splitWords(string $text): array
    {
        $iterator = IntlBreakIterator::createWordInstance($this->locale);
        $iterator->setText($text);

        $words = [];
        $start = $iterator->first();
        while (($end = $iterator->next()) !== IntlBreakIterator::DONE) {
            if ($iterator->getRuleStatus() >= IntlBreakIterator::WORD_NONE_LIMIT) {
                $words[] = substr($text, $start, $end - $start);
            }
            $start = $end;
        }
        return $words;
    }

    /**
     * Splits text into sentences using the locale's sentence-boundary rules.
     * @return string[]
     */
    public function splitSentences(string $text): array
    {
        $iterator = IntlBreakIterator::createSentenceInstance($this->locale);
        $iterator->setText($text);

        $sentences = [];
        $start = $iterator->first();
        while (($end = $iterator->next()) !== IntlBreakIterator::DONE) {
            $sentence = trim(substr($text, $start, $end - $start));
            if ($sentence !== '') {
                $sentences[] = $sentence;
            }
            $start = $end;
        }
        return $sentences;
    }

    /**
     * Truncates text to at most $max graphemes, breaking on a word boundary and
     * appending $ellipsis. Grapheme-aware, so it never splits a combining
     * sequence. Returns the original text if it already fits.
     */
    public function ellipsize(string $text, int $max, string $ellipsis = '…'): string
    {
        if (grapheme_strlen($text) <= $max) {
            return $text;
        }

        $budget = max(0, $max - grapheme_strlen($ellipsis));
        $head = grapheme_substr($text, 0, $budget);

        // Prefer to cut at the last word boundary that still fits.
        $iterator = IntlBreakIterator::createWordInstance($this->locale);
        $iterator->setText($head);
        $boundary = $iterator->preceding(strlen($head));

        $cut = $head;
        if ($boundary > 0 && $boundary !== IntlBreakIterator::DONE) {
            $candidate = rtrim(substr($head, 0, $boundary));
            if ($candidate !== '') {
                $cut = $candidate;
            }
        }
        return rtrim($cut) . $ellipsis;
    }

    /**
     * Locale-aware upper-casing (e.g. Turkish dotted/dotless I). Falls back to
     * the language-neutral ICU casing when the locale has no special rules.
     */
    public function upper(string $text): string
    {
        $language = Locale::getPrimaryLanguage($this->locale);
        $transliterator = Transliterator::create("$language-Upper") ?? Transliterator::create('Upper');
        return (string)$transliterator->transliterate($text);
    }

    /**
     * Locale-aware lower-casing.
     */
    public function lower(string $text): string
    {
        $language = Locale::getPrimaryLanguage($this->locale);
        $transliterator = Transliterator::create("$language-Lower") ?? Transliterator::create('Lower');
        return (string)$transliterator->transliterate($text);
    }

    /**
     * Runs an ICU transform over the text — script conversion, romanisation,
     * accent folding ('Any-Latin; Latin-ASCII' makes ASCII slugs).
     * @param string $text
     * @param string $id A compound ICU transliterator id; see
     *      supportedValues('transliterator') for the building blocks.
     * @return string
     * @throws Exception If $id is not a valid transliterator id.
     */
    public function transliterate(string $text, string $id): string
    {
        $transform = Transliterator::create($id)
            ?? throw new InvalidArgumentException("$id is not a valid transliterator id.");
        return (string)$transform->transliterate($text);
    }

    /**
     * Romanises text ('Москва' -> 'Moskva'); shorthand for the 'Any-Latin' transform.
     */
    public function romanize(string $text): string
    {
        return $this->transliterate($text, 'Any-Latin');
    }

    /**
     * Whether two strings are visually confusable ('paypal' vs a Cyrillic
     * 'раураl') per UTS #39. Locale-independent.
     */
    public function confusable(string $a, string $b): bool
    {
        return new SpoofChecker()->areConfusable($a, $b);
    }

    /**
     * Whether a string fails ICU's default spoof checks (mixed scripts,
     * restriction level, invisible characters) per UTS #39.
     */
    public function suspicious(string $text): bool
    {
        return new SpoofChecker()->isSuspicious($text);
    }

    /**
     * The LDML plural category a number falls into for this locale (e.g. 1 -> 'one',
     * 2 -> 'other' in English). Derived from ICU's plural rules via MessageFormatter
     * (PHP's intl exposes no PluralRules class).
     * @param int|float $value The number to categorise.
     * @param bool $ordinal Use ordinal rules (1st/2nd/3rd …) instead of cardinal.
     * @return string One of 'zero', 'one', 'two', 'few', 'many', 'other'.
     */
    public function pluralCategory(int|float $value, bool $ordinal = false): string
    {
        $keyword = $ordinal ? 'selectordinal' : 'plural';
        $pattern = "{n,$keyword,zero{zero}one{one}two{two}few{few}many{many}other{other}}";
        return MessageFormatter::formatMessage($this->locale, $pattern, ['n' => $value]);
    }

    /**
     * Week conventions for the locale: first day of the week, weekend days, and the
     * minimal days in the first week. Days are 1 = Monday … 7 = Sunday (ISO 8601),
     * matching the cosmo-js port.
     * @return array{firstDay:int, weekend:int[], minimalDays:int}
     */
    public function weekInfo(): array
    {
        $calendar = IntlCalendar::createInstance($this->modifiers['timeZone'], $this->locale);
        // ICU numbers days 1 = Sunday … 7 = Saturday; remap to ISO 1 = Monday … 7 = Sunday.
        $toIso = static fn(int $day): int => $day === IntlCalendar::DOW_SUNDAY ? 7 : $day - 1;

        $weekend = [];
        for ($day = IntlCalendar::DOW_SUNDAY; $day <= IntlCalendar::DOW_SATURDAY; $day++) {
            if ($calendar->getDayOfWeekType($day) !== IntlCalendar::DOW_TYPE_WEEKDAY) {
                $weekend[] = $toIso($day);
            }
        }
        sort($weekend);

        return [
            'firstDay' => $toIso($calendar->getFirstDayOfWeek()),
            'weekend' => $weekend,
            'minimalDays' => $calendar->getMinimalDaysInFirstWeek(),
        ];
    }

    /**
     * Localised month names (January … December), following the active calendar.
     * @param string $width 'none'/'long'/'full' (wide), 'medium' (abbreviated), or 'short' (narrow).
     * @return string[]
     * @throws Exception If $width is not valid.
     */
    public function monthNames(string $width = 'full'): array
    {
        $calendarType = $this->modifiers['calendar'] === 'gregorian' ? IntlDateFormatter::GREGORIAN : IntlDateFormatter::TRADITIONAL;
        $nameFormatter = new IntlDateFormatter($this->locale, IntlDateFormatter::NONE, IntlDateFormatter::NONE, 'UTC', $calendarType, str_repeat('M', $this->nameWidth($width)));

        // Non-Gregorian calendars don't line up with Gregorian months, so place each
        // name by the calendar's OWN month ordinal. The ordinal formatter must use the
        // same calendar the name formatter resolved (the locale can imply one, e.g.
        // fa-IR -> persian, with no modifier set).
        $resolvedCalendar = $nameFormatter->getCalendarObject()->getType();
        $ordinalFormatter = new IntlDateFormatter("en-US-u-ca-$resolvedCalendar", IntlDateFormatter::NONE, IntlDateFormatter::NONE, 'UTC', IntlDateFormatter::TRADITIONAL, 'M');

        $names = array_fill(0, 13, '');
        $base = gmmktime(12, 0, 0, 1, 1, 2023);
        for ($day = 0; $day < 400; $day++) {
            $timestamp = $base + $day * 86400;
            $index = (int)$ordinalFormatter->format($timestamp) - 1;
            if ($index >= 0 && $index < 13 && $names[$index] === '') {
                $names[$index] = $nameFormatter->format($timestamp);
            }
        }
        return array_values(array_filter($names, static fn(string $name): bool => $name !== ''));
    }

    /**
     * Localised weekday names, Sunday first (matching ICU symbol order).
     * @param string $width 'none'/'long'/'full' (wide), 'medium' (abbreviated), or 'short' (narrow).
     * @return string[]
     * @throws Exception If $width is not valid.
     */
    public function weekdayNames(string $width = 'full'): array
    {
        $formatter = new IntlDateFormatter($this->locale, IntlDateFormatter::NONE, IntlDateFormatter::NONE, 'UTC', IntlDateFormatter::GREGORIAN, str_repeat('E', $this->nameWidth($width)));

        $names = [];
        $sunday = gmmktime(12, 0, 0, 8, 1, 2021); // 2021-08-01 is a Sunday.
        for ($i = 0; $i < 7; $i++) {
            $names[] = $formatter->format($sunday + $i * 86400);
        }
        return $names;
    }

    /**
     * Localised display name of a time zone (e.g. "Australian Eastern Standard Time").
     * Defaults to the 'timeZone' modifier, falling back to the runtime zone.
     * @param string $style 'long' (default), 'short', 'shortOffset', 'longOffset',
     *                      'shortGeneric', or 'longGeneric'.
     * @throws Exception If $style is not valid.
     */
    public function timeZoneName(string $style = 'long'): string
    {
        $pattern = self::TZ_NAME_PATTERNS[$style] ?? throw new InvalidArgumentException("$style is not a valid time-zone name style.");
        $timeZone = $this->modifiers['timeZone'] ?? date_default_timezone_get();
        $formatter = new IntlDateFormatter($this->locale, IntlDateFormatter::NONE, IntlDateFormatter::NONE, $timeZone, IntlDateFormatter::GREGORIAN, $pattern);
        return $formatter->format(time());
    }

    /**
     * Scientific notation (e.g. 12345 -> "1.2345E4").
     */
    public function scientific(float $value): string
    {
        return new NumberFormatter($this->locale, NumberFormatter::SCIENTIFIC)->format($value);
    }

    /**
     * Compact notation (e.g. 1200 -> "1.2K", 1200000 -> "1.2M").
     * @param float $value
     * @param string $width 'long'/'full' for long form (e.g. "1.2 million"); anything
     *        else (including the default 'short') for the short form (e.g. "1.2M").
     * @return string
     */
    public function compact(float $value, string $width = 'short'): string
    {
        // ICU UNUM_COMPACT_LONG = 15, UNUM_COMPACT_DECIMAL (short) = 14;
        // PHP's intl extension binds these styles but does not expose named constants.
        $style = ($width === 'long' || $width === 'full') ? 15 : 14;
        return (new NumberFormatter($this->locale, $style))->format($value);
    }

    /**
     * Renders a directed (signed) duration in words: (-3, 'day') -> "3 days ago",
     * (2, 'hour') -> "in 2 hours". The directed counterpart of duration().
     *
     * Reconstructed from CLDR `fields` data (ext-intl binds no RelativeDateTimeFormatter).
     * With $numeric = 'auto', exact -1/0/1 offsets use the locale's word forms
     * ("yesterday", "today", "tomorrow") where CLDR provides them.
     *
     * @param int|float $amount  Signed: negative = past ("… ago"), positive = future ("in …").
     * @param string    $unit    second, minute, hour, day, week, month, quarter, or year.
     * @param string    $numeric 'always' (default, "1 day ago") or 'auto' ("yesterday").
     * @throws Exception If $unit is not a valid relative unit.
     */
    public function relativeDuration(int|float $amount, string $unit, string $numeric = 'always'): string
    {
        static $valid = ['second', 'minute', 'hour', 'day', 'week', 'month', 'quarter', 'year'];
        $key = strtolower($unit);
        if (!in_array($key, $valid, true)) {
            throw new InvalidArgumentException("\"$unit\" is not a valid relative unit (second, minute, hour, day, week, month, quarter, year).");
        }

        // 'auto': the locale's word form for an exact -1/0/1 offset, when CLDR has one.
        if ($numeric === 'auto') {
            $word = $this->get(Bundle::LOCALE, 'fields', $key, 'relative', (string)(int)$amount);
            if (is_string($word)) {
                return $word;
            }
        }

        // Otherwise the plural "in {0} days" / "{0} days ago" pattern.
        $bundle = $this->get(Bundle::LOCALE, 'fields', $key, 'relativeTime', $amount < 0 ? 'past' : 'future');
        if (!$bundle instanceof ResourceBundle) {
            throw new UnsupportedException("No relative-time data for \"$unit\" in locale \"{$this->locale}\".");
        }
        return MessageFormatter::formatMessage($this->locale, $this->bundleToPluralMessage($bundle), [abs($amount)]);
    }

    /**
     * Renders the directed duration between two moments as relative text, picking the
     * largest sensible unit (e.g. "in 5 days", "3 days ago"). Computes target − reference.
     *
     * @param mixed       $target    The moment being described (DateTimeInterface, IntlCalendar, Unix timestamp, or localtime() array).
     * @param mixed|null  $reference The moment to measure against; defaults to now.
     * @param string      $numeric   'auto' (default, allows "yesterday") or 'always'.
     */
    public function relativeDurationBetween(mixed $target, mixed $reference = null, string $numeric = 'auto'): string
    {
        $ref  = $reference === null ? time() : self::toTimestampMs($reference) / 1000;
        $diff = self::toTimestampMs($target) / 1000 - $ref;
        foreach ([[60, 'second'], [60, 'minute'], [24, 'hour'], [7, 'day'], [4.34524, 'week'], [12, 'month'], [INF, 'year']] as [$size, $unit]) {
            if (abs($diff) < $size) {
                return $this->relativeDuration((int)round($diff), $unit, $numeric);
            }
            $diff /= $size;
        }
        return $this->relativeDuration((int)round($diff), 'year', $numeric);
    }

    /**
     * Formats a numeric range (e.g. "3–5"). Built from the CLDR `range` pattern
     * ("{0}–{1}") since ext-intl binds no NumberRangeFormatter; unlike ICU's
     * formatter it does not collapse shared parts (always "3–5", never "3–5K").
     */
    public function numberRange(float $start, float $end): string
    {
        return str_replace(['{0}', '{1}'], [$this->number($start), $this->number($end)], $this->rangePattern());
    }

    /**
     * Formats a monetary range (e.g. "$3.00–$5.00"). Returns '' when no currency is
     * available. Like numberRange(), an approximation of ICU's NumberRangeFormatter.
     * @param string|null $code ISO 4217 code; defaults to the locale/region currency.
     */
    public function moneyRange(float $start, float $end, ?string $code = null): string
    {
        $code = $code ?: $this->modifiers['currency'];
        if (!$code) {
            return '';
        }
        return str_replace(['{0}', '{1}'], [$this->money($start, $code), $this->money($end, $code)], $this->rangePattern());
    }

    /** The locale's CLDR number range pattern ("{0}–{1}"), falling back to root's. */
    private function rangePattern(): string
    {
        $sys = $this->get(Bundle::LOCALE, 'NumberElements', 'default') ?: 'latn';
        return $this->get(Bundle::LOCALE, 'NumberElements', $sys, 'miscPatterns', 'range') ?: '{0}–{1}';
    }

    /**
     * Joins a list using the locale's conventions (e.g. "A, B, and C"). Built from
     * CLDR listPattern data — PHP reaches ICU directly, so no Intl.ListFormat is needed.
     * @param array $items The items to join.
     * @param string $type 'conjunction' (and, default), 'disjunction' (or), or 'unit'.
     * @param string $width 'none'/'long'/'full' (wide), 'medium' (short), or 'short' (narrow).
     * @throws Exception If $type or $width is not valid.
     */
    public function join(array $items, string $type = 'conjunction', string $width = 'full'): string
    {
        $key = self::LIST_TYPES[$type] ?? throw new InvalidArgumentException("$type is not a valid list type (conjunction, disjunction, unit).");
        $suffix = self::LIST_WIDTHS[$width] ?? throw new InvalidArgumentException("$width is not a valid width for list formatting.");

        $items = array_map(strval(...), array_values($items));
        $count = count($items);
        if ($count === 0) return '';
        if ($count === 1) return $items[0];

        $pattern = fn(string $piece): string => $this->listPattern($key, $suffix, $piece);
        if ($count === 2) return $this->applyListPattern($pattern('2'), $items[0], $items[1]);

        $result = $this->applyListPattern($pattern('end'), $items[$count - 2], $items[$count - 1]);
        for ($i = $count - 3; $i >= 1; $i--) {
            $result = $this->applyListPattern($pattern('middle'), $items[$i], $result);
        }
        return $this->applyListPattern($pattern('start'), $items[0], $result);
    }

    /**
     * Resolves one CLDR list-pattern piece ('2', 'start', 'middle', 'end'), falling
     * back from the width-specific variant to the base type to 'standard'. Each
     * lookup goes through get() so a partial locale override (e.g. en_AU defines only
     * 'end' for 'standard') still inherits the remaining pieces from the parent locale.
     */
    private function listPattern(string $key, string $suffix, string $piece): string
    {
        return (string)(
            $this->get(Bundle::LOCALE, 'listPattern', $key . $suffix, $piece)
            ?? $this->get(Bundle::LOCALE, 'listPattern', $key, $piece)
            ?? $this->get(Bundle::LOCALE, 'listPattern', 'standard', $piece)
        );
    }

    private function applyListPattern(string $pattern, string $first, string $second): string
    {
        return str_replace(['{0}', '{1}'], [$first, $second], $pattern);
    }

    private function nameWidth(string $width): int
    {
        return self::NAME_WIDTHS[$width] ?? throw new InvalidArgumentException("$width is not a valid width.");
    }

    /**
     * Generic localised display name — a single entry point over the dedicated lookups.
     * @param string $type 'language', 'region', 'script', 'calendar', or 'currency'.
     * @param string $code The code to translate (e.g. 'en', 'AU', 'Hans', 'buddhist', 'EUR').
     * @throws Exception If $type is not recognised.
     */
    public function displayName(string $type, string $code): string
    {
        return match ($type) {
            'language' => $this->language($code),
            'region' => $this->country($code),
            'script' => $this->script($code),
            'calendar' => $this->calendar($code),
            'currency' => $this->currency($code),
            default => throw new InvalidArgumentException("$type is not a display-name type (language, region, script, calendar, currency)."),
        };
    }

    /**
     * Splits text into grapheme clusters (user-perceived characters), so combining
     * marks and emoji ZWJ sequences stay intact.
     * @return string[]
     */
    public function splitGraphemes(string $text): array
    {
        $iterator = IntlBreakIterator::createCharacterInstance($this->locale);
        $iterator->setText($text);

        $graphemes = [];
        $start = $iterator->first();
        while (($end = $iterator->next()) !== IntlBreakIterator::DONE) {
            $graphemes[] = substr($text, $start, $end - $start);
            $start = $end;
        }
        return $graphemes;
    }

    /**
     * The values the runtime's ICU supports for a given key. PHP's intl surface
     * enumerates 'timeZone' (IntlTimeZone), 'currency' (CLDR currency bundle) and
     * 'transliterator' (Transliterator::listIDs); other keys ('calendar',
     * 'collation', 'numberingSystem', 'unit') are not reachable through it and
     * raise rather than return a hardcoded list.
     * @param string $key 'timeZone', 'currency', or 'transliterator'.
     * @return string[]
     * @throws Exception If $key is not supported.
     */
    public function supportedValues(string $key): array
    {
        switch ($key) {
            case 'timeZone':
                return iterator_to_array(IntlTimeZone::createEnumeration(), false);
            case 'currency':
                $currencies = $this->get(Bundle::CURRENCY, 'Currencies');
                $codes = [];
                foreach ($currencies as $code => $_) {
                    $codes[] = $code;
                }
                return $codes;
            case 'transliterator':
                return Transliterator::listIDs();
            case 'calendar':
            case 'collation':
            case 'numberingSystem':
            case 'unit':
                throw new UnsupportedException("supportedValues(\"$key\") is not available through PHP's intl surface.");
            default:
                throw new InvalidArgumentException("$key is not a valid key (timeZone, currency, transliterator).");
        }
    }

    /**
     * Convert a mixed date/time value to a Unix timestamp in milliseconds (for IntlCalendar::setTime()).
     */
    private static function toTimestampMs(mixed $value): float
    {
        if ($value instanceof \DateTimeInterface) return (float)($value->getTimestamp() * 1000);
        if ($value instanceof IntlCalendar) return $value->getTime();
        if (is_array($value)) {
            // localtime() array
            $ts = mktime($value['tm_hour'] ?? 0, $value['tm_min'] ?? 0, $value['tm_sec'] ?? 0,
                ($value['tm_mon'] ?? 0) + 1, $value['tm_mday'] ?? 1, ($value['tm_year'] ?? 0) + 1900);
            return (float)($ts * 1000);
        }
        return (float)($value * 1000);
    }

    /**
     * Split a CLDR interval format pattern into its start and end sub-patterns.
     *
     * An interval pattern lays out both endpoints back to back and repeats one field,
     * e.g. "MMM d – d, y" or "E, MMM d – E, MMM d, y". The second endpoint begins at
     * the first pattern letter that has already appeared earlier (ICU's own rule), so
     * everything before that — including the locale's separator — belongs to the first
     * endpoint. This needs no separator data, so it is robust to CLDR quirks like the
     * Spanish " al " separator or Bosnian's fallback/​pattern hyphen-vs-dash mismatch.
     *
     * Examples:
     *   splitIntervalPattern("MMM d – d, y")          → ["MMM d – ",    "d, y"]
     *   splitIntervalPattern("d.–d. MMM y")           → ["d.–",         "d. MMM y"]
     *   splitIntervalPattern("E, MMM d – E, MMM d, y") → ["E, MMM d – ", "E, MMM d, y"]
     */
    private static function splitIntervalPattern(string $pattern): array
    {
        static $patternLetters = 'GgYyUrQqMLlwWdDFEecaHhKkmsSAzZOvVXxB';
        $seen = [];
        $len  = strlen($pattern);
        $i    = 0;
        while ($i < $len) {
            $byte = $pattern[$i];
            if ($byte === "'") {  // skip quoted literal text
                $i++;
                while ($i < $len && $pattern[$i] !== "'") $i++;
                if ($i < $len) $i++;
                continue;
            }
            if (ord($byte) < 0x80 && strpos($patternLetters, $byte) !== false) {
                $runStart = $i;
                while ($i < $len && $pattern[$i] === $byte) $i++;
                if (isset($seen[$byte])) {
                    return [substr($pattern, 0, $runStart), substr($pattern, $runStart)];
                }
                $seen[$byte] = true;
            } else {
                $i++;
            }
        }
        return [$pattern, ''];  // no field recurs (not seen in CLDR interval data)
    }

    /**
     * Applies a portable NumberOptions array to a NumberFormatter.
     *
     * JS-only options ('signDisplay', 'trailingZeroDisplay', 'roundingPriority',
     * 'notation', 'compactDisplay') have no equivalent in ICU's legacy
     * NumberFormatter and are silently ignored here, matching the documented contract.
     */
    private function applyNumberOptions(NumberFormatter $formatter, array $options): void
    {
        foreach ($options as $key => $_) {
            if (!isset(self::KNOWN_NUMBER_OPTIONS[$key])) {
                throw new InvalidArgumentException("\"$key\" is not a valid number option.");
            }
        }
        // ICU's NumberFormatter defaults to HALF_EVEN (banker's rounding) whereas the
        // JS port inherits Intl.NumberFormat's HALF_EXPAND (round half away from zero).
        // Default to HALF_EXPAND so money/number/percentage round identically across
        // ports; an explicit 'roundingMode' option below still overrides this.
        $formatter->setAttribute(NumberFormatter::ROUNDING_MODE, NumberFormatter::ROUND_HALFUP);
        if (isset($options['minimumIntegerDigits'])) {
            $formatter->setAttribute(NumberFormatter::MIN_INTEGER_DIGITS, $options['minimumIntegerDigits']);
        }
        if (isset($options['minimumFractionDigits'])) {
            $formatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, $options['minimumFractionDigits']);
        }
        if (isset($options['maximumFractionDigits'])) {
            $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $options['maximumFractionDigits']);
        }
        // ICU treats significant-digit limits as mutually exclusive with fraction
        // digits; enabling either turns the significant-digit mode on.
        if (isset($options['minimumSignificantDigits'])) {
            $formatter->setAttribute(NumberFormatter::SIGNIFICANT_DIGITS_USED, 1);
            $formatter->setAttribute(NumberFormatter::MIN_SIGNIFICANT_DIGITS, $options['minimumSignificantDigits']);
        }
        if (isset($options['maximumSignificantDigits'])) {
            $formatter->setAttribute(NumberFormatter::SIGNIFICANT_DIGITS_USED, 1);
            $formatter->setAttribute(NumberFormatter::MAX_SIGNIFICANT_DIGITS, $options['maximumSignificantDigits']);
        }
        if (array_key_exists('useGrouping', $options)) {
            $formatter->setAttribute(NumberFormatter::GROUPING_USED, $options['useGrouping'] ? 1 : 0);
        }
        if (isset($options['roundingMode'])) {
            $formatter->setAttribute(
                NumberFormatter::ROUNDING_MODE,
                self::ROUNDING_MODES[$options['roundingMode']]
                    ?? throw new InvalidArgumentException("{$options['roundingMode']} is not a valid rounding mode."),
            );
        }
        if (isset($options['roundingIncrement'])) {
            // Match the Intl contract: the increment is in units of the last fraction
            // digit (5 at 2 digits → step 0.05), not the literal step ICU expects.
            $scale = $options['maximumFractionDigits'] ?? 0;
            $formatter->setAttribute(NumberFormatter::ROUNDING_INCREMENT, $options['roundingIncrement'] * (10 ** -$scale));
        }
    }

    /** Applies portable collation tailoring ('numeric', 'caseFirst') to a Collator. */
    private function applyCollationOptions(Collator $collator, array $options): void
    {
        foreach ($options as $key => $_) {
            if (!isset(self::KNOWN_COLLATION_OPTIONS[$key])) {
                throw new InvalidArgumentException("\"$key\" is not a valid collation option.");
            }
        }
        if (array_key_exists('numeric', $options)) {
            $collator->setAttribute(Collator::NUMERIC_COLLATION, $options['numeric'] ? Collator::ON : Collator::OFF);
        }
        if (isset($options['caseFirst'])) {
            $collator->setAttribute(Collator::CASE_FIRST, match ($options['caseFirst']) {
                'upper' => Collator::UPPER_FIRST,
                'lower' => Collator::LOWER_FIRST,
                'false' => Collator::OFF,
                default => throw new InvalidArgumentException("{$options['caseFirst']} is not a valid caseFirst value."),
            });
        }
    }

    #endregion
}
