<?php
/**
 * Created by Reza Salarmehr.
 */

declare(strict_types=1);

namespace Salarmehr;

class Cosmopolitan
{
    public $locale;
    private $timezone;
    private $useLocaleCalender;

    const NONE = \IntlDateFormatter::NONE;
    const SHORT = \IntlDateFormatter::SHORT;
    const MEDIUM = \IntlDateFormatter::MEDIUM;
    const LONG = \IntlDateFormatter::LONG;
    const FULL = \IntlDateFormatter::FULL;

    public function __construct(string $locale = null, string $timezone = null, bool $userLocalCalender = true)
    {
        $this->locale = $locale ?: \Locale::getDefault();
        $this->timezone = $timezone;

        // use the local calendar system in the countries which use non-gregorian calendar.
        $this->useLocaleCalender = $userLocalCalender;
    }

    /**
     * Translate the language of this locale (e.g. En -> English)
     * @param $locale
     * @return string
     */
    public function language($locale): string
    {
        return \Locale::getDisplayLanguage($locale, $this->locale);
    }

    /**
     * Translate the country of a locale (e.g. AU -> Australia)
     * @param string $local ISO 3166 country codes or a valid locale
     * @return string
     */
    public function country(string $local): string
    {
        if (!preg_match('#[-_]#', $local)) $local = '-' . $local;
        return \Locale::getDisplayRegion($local, $this->locale);
    }

    public function message(string $message, array $args): string
    {
        return \MessageFormatter::formatMessage($this->locale, $message, $args);
    }

    public function quote(string $quote): string
    {
        return $this->bundle()->get('delimiters')->get('quotationStart') . $quote . $this->bundle()->get('delimiters')->get('quotationEnd');
    }

    public function bundle($name = null): \ResourceBundle
    {
        return \ResourceBundle::create($this->locale, $name);
    }

    /**
     * @param float $value
     * @param string $currency
     * The 3-letter ISO 4217 currency code indicating the currency to use.
     * @param string|null $pattern
     * @return string
     */
    public function currency(float $value, string $currency, string $pattern = ''): string
    {
        return (new \NumberFormatter($this->locale, \NumberFormatter::CURRENCY, $pattern))->formatCurrency($value, $currency);
    }

    /**
     * @param float $value
     * @param int $precision
     * @return string
     */
    public function percentage(float $value, int $precision = 3): string
    {
        $formatter = new \NumberFormatter($this->locale, \NumberFormatter::PERCENT);
        $formatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $precision);
        return $formatter->format($value);
    }

    public function number(float $number)
    {
        return (new \NumberFormatter($this->locale, \NumberFormatter::DEFAULT_STYLE))->format($number);
    }

    public function ordinal(int $number)
    {
        return (new \NumberFormatter($this->locale, \NumberFormatter::ORDINAL))->format($number);
    }

    public function spellout(float $number)
    {
        return (new \NumberFormatter($this->locale, \NumberFormatter::SPELLOUT))->format($number);
    }

    public function duration(float $duration): string
    {
        return $number = \NumberFormatter::create($this->locale, \NumberFormatter::DURATION)->format($duration);
    }

    private function verify($local): void
    {
        $local = preg_replace('#-#', '_', $local);
        if (!in_array($local, \ResourceBundle::getLocales(''))) {
            throw new IntlException("Invalid locale $local");
        }
    }

    /**
     * https://unicode-org.github.io/icu-docs/apidoc/released/icu4c/classSimpleDateFormat.html#details
     * @param $value
     * @param $format
     * @return false|string
     */
    public function customTime($value, string $format): string
    {
        $formatter = new \IntlDateFormatter($this->locale, null, null, $this->timezone, $this->calendarType, $format);
        return $formatter->format($value);
    }

    public function datetime($value, int $datetype = self::SHORT, int $timetype = self::MEDIUM): string
    {
        $calendarType = $this->useLocaleCalender ? \IntlDateFormatter::TRADITIONAL : \IntlDateFormatter::GREGORIAN;
        $formatter = new \IntlDateFormatter($this->locale, $datetype, $timetype, $this->timezone, $calendarType);
        return $formatter->format($value);
    }

    public function date($value, int $length = self::SHORT): string
    {
        return $this->datetime($value, $length, self::NONE);
    }

    public function time($value, int $length = self::MEDIUM): string
    {
        return $this->datetime($value, self::NONE, $length);
    }
}