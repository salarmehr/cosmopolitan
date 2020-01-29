<?php
/**
 * Created by Reza Salarmehr.
 */
declare(strict_types=1);

namespace Salarmehr\Cosmopolitan;

use IntlDateFormatter;
use Locale;
use MessageFormatter;
use NumberFormatter;
use ResourceBundle;

require_once "Exception.php";
require_once "Bundle.php";

class Intl extends Locale
{
    const NONE = IntlDateFormatter::NONE;
    const SHORT = IntlDateFormatter::SHORT;
    const MEDIUM = IntlDateFormatter::MEDIUM;
    const LONG = IntlDateFormatter::LONG;
    const FULL = IntlDateFormatter::FULL;

    const TYPES = [
        'none'   => self::NONE,
        'short'  => self::SHORT,
        'medium' => self::MEDIUM,
        'long'   => self::LONG,
        'full'   => self::FULL,

        'n'   => self::NONE,
        's'  => self::SHORT,
        'm' => self::MEDIUM,
        'l'   => self::LONG,
        'f'   => self::FULL,
    ];

    public $locale;

    public $defaults = [
        'useLocalCalender' => true, // should use the official nation calendar or overwrite it with Gregorian
        'fallback'         => true,
    ];

    private $timezone;
    private $useLocalCalender;

    /**
     * Intl constructor.
     * @param string|null $locale e.g. en_AU
     * @param string|null $timezone e.g. 'Australia/Sydney
     * @param array $options
     */
    public function __construct(string $locale = null, string $timezone = null, array $options = [])
    {
        $options = $options + $this->defaults;
        $this->locale = Locale::canonicalize($locale ?: Locale::getDefault());
        $this->timezone = $timezone;

        $this->useLocalCalender = $options['useLocalCalender'];
    }

    public static function create(string $locale = null, string $timezone = null, array $options = [])
    {
        return new self($locale, $timezone, $options);
    }

    /**
     * @param string $bundleName
     * @param string $key
     * @return ResourceBundle
     * @throws Exception
     */
    public function get(string $bundleName, string $key)
    {
        return (new Bundle($this->locale, $bundleName, true))->get($key);
    }

    #region key -> value functions

    /**
     * @param string $currency The 3-letter ISO 4217 currency code indicating the currency to use.
     * @param bool $symbol
     * @return string
     * @throws Exception
     */
    public function currency(string $currencyCode, bool $symbol = false, bool $strict = false): string
    {
        $currencyCode = strtoupper($currencyCode);
        $currency = $this->get(Bundle::CURRENCY, 'Currencies')->get($currencyCode);

        if ($currency === null)
            if ($strict)
                throw new Exception("$currencyCode is no a valid currency code");
            else
                return $currencyCode;

        return $symbol ? $currency->get(0) : $currency->get(1);
    }

    /**
     * Translate a language identifier (e.g. En -> English, glk -> Gilaki)
     * If you have a locale identifier (en-Au) instead of language
     * use \Locale::getPrimaryLanguage($locale) to extract the language
     * @param $language
     * @return string
     * @throws Exception
     */
    public function language(string $language): string
    {
        return Locale::getDisplayLanguage($language, $this->locale);
    }

    /**
     * Translate the country of a locale (e.g. AU -> Australia)
     * @param string $country ISO 3166 country codes or a valid locale
     * @return string
     */
    public function country(string $country): string
    {
        if (!preg_match('#[-_]#', $country)) $country = '-' . $country;
        return Locale::getDisplayRegion($country, $this->locale);
    }

    /**
     * Translate the scriptidentifier (e.g. 'zh_Hans' -> 'Simplified Chinese')
     * @param $script
     * @return string
     * @throws Exception
     */
    public function script(string $script): string
    {
        return $this->get(Bundle::LANGUAGE, 'Scripts')->get(ucwords($script));
    }

    /**
     * Translate the calendar identifier (e.g. "buddhist" -> "Buddhist Calendar")
     * @param $calendar
     * @return string
     * @throws Exception
     */
    public function calendar(string $calendar): string
    {
        return $this->get(Bundle::LANGUAGE, 'Types')->get('calendar')->get($calendar);
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
     * @param float $value
     * @param string $currency The 3-letter ISO 4217 currency code indicating the currency to use.
     * @param string|null $pattern
     * @return string
     */
    public function money(float $value, string $currency, string $pattern = ''): string
    {
        return (new NumberFormatter($this->locale, NumberFormatter::CURRENCY, $pattern))->formatCurrency($value, $currency);
    }

    /**
     * @param float $value
     * @param int $precision
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

    public function spellout(float $number): string
    {
        return (new NumberFormatter($this->locale, NumberFormatter::SPELLOUT))->format($number);
    }

    /**
     * @param float $duration
     * @param bool $withWords this currently works for English, for other languages it has no effect on output
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

    /**
     * https://unicode-org.github.io/icu-docs/apidoc/released/icu4c/classSimpleDateFormat.html#details
     * @param $value
     * @param $format
     * @return false|string
     */
    public function customTime($value, string $format): string
    {
        $formatter = new IntlDateFormatter($this->locale, null, null, $this->timezone, $this->calendarType, $format);
        return $formatter->format($value);
    }

    private function getTimeType(string $type): int
    {
        if (!array_key_exists($type, self::TYPES)) {
            throw new Exception("$type is not a valid type for time formatting");
        }
        return self::TYPES[$type];
    }

    public function moment($value, string $dateType = 'short', string $timeType = 'short'): string
    {
        $dateType = $this->getTimeType($dateType);
        $timeType = $this->getTimeType($timeType);

        $calendarType = $this->useLocalCalender ? IntlDateFormatter::TRADITIONAL : IntlDateFormatter::GREGORIAN;
        $formatter = new IntlDateFormatter($this->locale, $dateType, $timeType, $this->timezone, $calendarType);
        return $formatter->format($value);
    }

    public function date($value, string $type = 'short'): string
    {
        return $this->moment($value, $type, 'none');
    }

    public function time($value, string $type = 'short'): string
    {
        return $this->moment($value, 'none', $type);
    }
}