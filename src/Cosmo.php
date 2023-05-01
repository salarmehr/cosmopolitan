<?php
/**
 * Created by Reza Salarmehr.
 */
declare(strict_types=1);

namespace Salarmehr\Cosmopolitan;

use IntlChar;
use IntlDateFormatter;
use Locale;
use MessageFormatter;
use NumberFormatter;
use ResourceBundle;

require_once "Exception.php";
require_once "Bundle.php";

class Cosmo extends Locale
{
    const NONE = IntlDateFormatter::NONE;
    const SHORT = IntlDateFormatter::SHORT;
    const MEDIUM = IntlDateFormatter::MEDIUM;
    const LONG = IntlDateFormatter::LONG;
    const FULL = IntlDateFormatter::FULL;

    const TIME_TYPES = [
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

    const UNITE_TYPES = [
        'short' => 'unitsNarrow',
        'medium' => 'unitsShort',
        'long' => 'units',
        'full' => 'units',

        's' => 'unitsNarrow',
        'm' => 'unitsShort',
        'l' => 'units',
        'f' => 'units',
    ];

    public $locale;

    // https://tools.ietf.org/rfc/bcp/bcp47#section-2.1
    public $subtags = [
        'language' => '',
        'script' => '',
        'region' => '',
    ];

    public $modifiers = [
        'calendar' => null, // when null, the common calendar of the locale will be used (Gregorian for most countries), see the moment() calendar param
        'currency' => '',
        'timezone' => null,
    ];

    /**
     * @param string|null $locale e.g. en_AU
     * @param array       $modifiers
     */
    public function __construct(string $locale = null, array $modifiers = [])
    {
        $this->locale = Locale::canonicalize($locale ?: Locale::getDefault());
        $this->modifiers = $modifiers + $this->modifiers;
        $this->subtags = Locale::parseLocale($this->locale) + $this->subtags;

        if ($this->subtags['region'] && !$this->modifiers['currency']) {
            $this->modifiers['currency'] = (new NumberFormatter($this->locale, NumberFormatter::CURRENCY))->getTextAttribute(NumberFormatter::CURRENCY_CODE);
        }
    }

    public static function create(string $locale = null, array $modifiers = []): Cosmo
    {
        return new self($locale, $modifiers);
    }

    /**
     * Instead of locale string you provide an array of locale subtags
     * @param array $subtags
     * @param array $modifiers
     * @return Cosmo
     * @see Locale::composeLocale() for the input array format
     */
    public static function createFromSubtags(array $subtags, array $modifiers = []): Cosmo
    {
        return new self(Locale::composeLocale($subtags), $modifiers);
    }

    public static function createFromHttp(?string $header = null, array $modifiers = []): Cosmo
    {
        $header = $header ?: $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null;
        return new self(Locale::acceptFromHttp($header), $modifiers);
    }


    /**
     * @param string $bundleName
     * @param array  $path
     * @return ResourceBundle|string
     */
    public function get(string $bundleName, ...$path)
    {
        return $this->extract($this->locale, $bundleName, $path)
            ?: $this->extract(Locale::getPrimaryLanguage($this->locale), $bundleName, $path)
                ?: $this->extract('root', $bundleName, $path);
    }

    private function extract($local, $bundleName, array $path)
    {
        $current = Bundle::create($local, $bundleName, true);
        foreach ($path as $item) {
            try {
                $current = $current->get($item);
            }
            catch (\Exception $exception){
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
     * @param ?string $currencyCode The 3-letter ISO 4217 currency code indicating the currency to use.
     * @param bool   $getSymbol
     * @param bool   $strict
     * @return string
     * @throws Exception
     */
    public function currency(?string $currencyCode = null, bool $getSymbol = false, bool $strict = false): string
    {
        $currencyCode = count(func_get_args()) == 0 ? $this->modifiers['currency'] : (string)$currencyCode;
        $currencyCode = strtoupper($currencyCode);

        $currency = $this->get(Bundle::CURRENCY, 'Currencies', $currencyCode);

        if ($currency === null)
            if ($strict)
                throw new Exception("$currencyCode is no a valid currency code");
            else
                return $currencyCode;

        return $getSymbol ? $currency->get(0) : $currency->get(1);
    }

    /**
     * Translate a language identifier (e.g. En -> English, glk -> Gilaki)
     * If you have a locale identifier (en-Au) instead of language
     * use \Locale::getPrimaryLanguage($locale) to extract the language
     * @param ?string $language
     * @return string
     */
    public function language(?string $language = null): string
    {
        $language = count(func_get_args()) == 0 ? $this->locale : $language;
        // if the language is null or 'getDisplayLanguage' does not work as expected and returns the current local
        if ($language === null || $language === '') return '';
        return Locale::getDisplayLanguage($language, $this->locale);
    }

    public function direction(?string $language = null): string
    {
        $language = count(func_get_args()) == 0 ? $this->locale : (string)$language;

        try {
            $dir = Bundle::create($language, Bundle::LOCALE, true)['layout']['characters'] ?? null;
            return $dir == 'right-to-left' ? 'rtl' : 'ltr';
        }
        catch (\Exception $exception) {
            return 'ltr';
        }
    }

    /**
     * Translate the country of a locale (e.g. AU -> Australia)
     * @param ?string $country ISO 3166 country codes or a valid locale
     * @return string
     */
    public function country(?string $country = null): string
    {
        if (count(func_get_args()) == 0) {
            $country = $this->subtags['region'];
        } elseif (!$country) {
            return '';
        }

        if (!preg_match('#[-_]#', (string)$country)) {
            $country = '_' . $country;
        }
        return Locale::getDisplayRegion($country, $this->locale);
    }

    /**
     * Returns the emoji of a locale (e.g. AU -> 🇦🇺)
     * @param ?string $country ISO 3166 country codes or a valid locale
     * @return string
     */
    public function flag(?string $country = null): string
    {
        if (count(func_get_args()) == 0) {
            $country = $this->subtags['region'];
        }

        $country = strtoupper($country);

        // 127397 is flag offset (0x1F1E6) mines ascii offset (0x41)
        return IntlChar::chr(ord($country[0]) + 127397)
            . IntlChar::chr(ord($country[1]) + 127397);
    }

    /**
     * Translate the script identifier (e.g. 'zh_Hans' -> 'Simplified Chinese')
     * If no parameter is send and the scrip subtag is presented on the locale identifier, it will be used as the input
     * @param ?string $script
     * @return string
     */
    public function script(?string $script = null): string
    {
        if (count(func_get_args()) == 0) {
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

    public function message(string $message, array $args): string
    {
        return MessageFormatter::formatMessage($this->locale, $message, $args);
    }

    public function quote(string $quote): string
    {
        $delimiters = $this->get(Bundle::LOCALE, 'delimiters');
        return $delimiters->get('quotationStart') . $quote . $delimiters->get('quotationEnd');
    }

    /**
     * @param float   $value
     * @param ?string $currency  The 3-letter ISO 4217 currency code indicating the currency to use.
     * @param ?int    $precision The needed number of decimals digits
     * @param string  $pattern
     * @return string
     * @throws Exception
     */
    public function money(float $value, ?string $currency = null, string $pattern = '', ?int $precision = null): string
    {
        $currency = $currency ?: $this->modifiers['currency'];
        if (!$currency) {
            throw new Exception("No currency is set to format the monetary value. 
                        Set the region subtag in the local identifier (e.g. en -> en_AU) or provide a valid currency code parameter.");
        }

        $formatter = new NumberFormatter($this->locale, NumberFormatter::CURRENCY, $pattern);
        $formatter->setTextAttribute($formatter::CURRENCY_CODE, $currency);

        if ($precision !== null) {
            $formatter->setAttribute($formatter::FRACTION_DIGITS, $precision);
        }

        return $formatter->format($value);
    }

    /**
     * @param float $value
     * @param int   $precision
     * @return string
     */
    public function percentage(float $value, int $precision = 3): string
    {
        $formatter = new NumberFormatter($this->locale, NumberFormatter::PERCENT);
        $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $precision);
        return $formatter->format($value);
    }

    public function number(float $number): string
    {
        return (new NumberFormatter($this->locale, NumberFormatter::DEFAULT_STYLE))->format($number);
    }

    public function ordinal(int $number): string
    {
        return (new NumberFormatter($this->locale, NumberFormatter::ORDINAL))->format($number);
    }

    /**
     * Get a symbol value
     * @link https://php.net/manual/en/numberformatter.getsymbol.php
     * @param int|string $symbol <p>
     * Symbol specifier, one of the format symbol constants.
     * </p>
     * @return string|false The symbol string or <b>FALSE</b> on error.
     */
    public function symbol($symbol): string
    {
        if(is_string($symbol)){
            $symbol = strtoupper($symbol);
            $symbol= constant("NumberFormatter::{$symbol}_SYMBOL");
        }
        return (new NumberFormatter($this->locale, NumberFormatter::DECIMAL))->getSymbol($symbol);
    }


    public function spellout(float $number): string
    {
        return (new NumberFormatter($this->locale, NumberFormatter::SPELLOUT))->format($number);
    }

    /**
     * @param float $duration
     * @param bool  $withWords this currently works for English, for other languages it has no effect on output
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
        if (!array_key_exists($type, self::TIME_TYPES)) {
            throw new Exception("$type is not a valid type for time formatting.");
        }
        return self::TIME_TYPES[$type];
    }

    /**
     * Localise time, date, or date+time. Allowed types are none, short, medium, long and full.
     * @param mixed   $value        Value to format. This may be
     *                              a DateTimeInterface object,
     *                              an IntlCalendar object,
     *                              a numeric type representing a (possibly fractional) number of seconds since epoch
     *                              or an array in the format output by localtime().
     *                              If a DateTime or an IntlCalendar object is passed, its timezone is not considered. The object will be formatted using the formaterʼs configured timezone. If one wants to use the timezone of the object to be formatted, IntlDateFormatter::setTimeZone() must be called before with the objectʼs timezone. Alternatively, the static function IntlDateFormatter::formatObject() may be used instead.
     * @param string  $dateType
     * @param string  $timeType
     * @param ?string $calendar     currently only this two values are supported.
     *                              null: uses the common calendar of the local e.g. Persian Calendar for Iran, and Gregorian Australia
     *                              'gregorian': will use this calendar to display temporal values
     * @param ?string $pattern
     * @return string
     * @throws Exception
     */
    public function moment($value, string $dateType = 'short', string $timeType = 'short', ?string $calendar = null, ?string $pattern = null): string
    {
        $calendar = $calendar ?: $this->modifiers['calendar'] == null;

        $dateType = $this->getTimeType($dateType);
        $timeType = $this->getTimeType($timeType);
        $calendarType = $calendar === 'gregorian' ? IntlDateFormatter::GREGORIAN : IntlDateFormatter::TRADITIONAL;
        $pattern = $pattern ?: ''; // IntlDateFormatter does not accept null for this param

        $formatter = new IntlDateFormatter($this->locale, $dateType, $timeType, $this->modifiers['timezone'], $calendarType, $pattern);
        $result = $formatter->format($value);
        if (intl_is_failure($formatter->getErrorCode())) {
            throw new Exception($formatter->getErrorMessage(), $formatter->getErrorCode());
        }
        return $result;
    }

    /**
     * Use this function to have a formatted data time or get the individual time components (zaman means time)
     * @param mixed       $value
     * @param string      $pattern see https://unicode-org.github.io/icu-docs/apidoc/released/icu4c/classSimpleDateFormat.html#details
     * @param string|null $calendar
     * @return false|string
     * @throws Exception
     */
    public function customTime($value, string $pattern, ?string $calendar = null): string
    {
        return $this->moment($value, 'none', 'none', $calendar, $pattern);
    }

    public function date($value, string $type = 'short'): string
    {
        return $this->moment($value, $type, 'none');
    }

    public function time($value, string $type = 'short'): string
    {
        return $this->moment($value, 'none', $type);
    }

    /**
     * Localise nearly all units and scale see https://intl.rmcreative.ru/site/unit-data?locale=en for the list of possible units and scales
     * This method is in experimental stage
     * @param        $unit
     * @param        $scale
     * @param        $value
     * @param string $type
     * @return string
     * @throws Exception
     */
    public function unit($unit, $scale, $value, string $type = 'full'): string
    {
        if (!array_key_exists($type, self::UNITE_TYPES)) {
            throw new Exception("$type is not a valid type for unit formatting.");
        }

        $bundle = $this->get('ICUDATA-unit', self::UNITE_TYPES[$type], $unit, $scale);
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
