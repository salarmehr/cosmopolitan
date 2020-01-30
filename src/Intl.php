<?php
/**
 * Created by Reza Salarmehr.
 */
declare(strict_types=1);

namespace Salarmehr\Cosmopolitan;

class Intl
{
    const NONE = \IntlDateFormatter::NONE;
    const SHORT = \IntlDateFormatter::SHORT;

    // should use the official nation calendar or overwrite it with Gregorian
    const MEDIUM = \IntlDateFormatter::MEDIUM;
    const LONG = \IntlDateFormatter::LONG;
    const FULL = \IntlDateFormatter::FULL;
    public $locale;
    public $defaults = [
        'useLocalCalender' => true,
        'bundleName' => null,
        'fallback' => true,
    ];
    private $timezone;
    private $useLocalCalender;

    public function __construct(string $locale = null, string $timezone = null, array $options = []) {
        $options = $options + $this->defaults;
        $this->locale = $locale ?: \Locale::getDefault();
        $this->timezone = $timezone;

        $this->useLocalCalender = $options['useLocalCalender'];
    }

    public function get(\ResourceBundle $bundle, string $key) {
        $output = $bundle->get($key);
        if ($output == null && intl_is_failure($bundle->getErrorCode())) {
            throw new Exception($bundle->getErrorMessage());
        }
        return $output;
    }

    private function getBundle(string $bundle): \ResourceBundle {
        return \ResourceBundle::create($this->locale, $bundle);
    }

    #region key/value functions

    /**
     * @param string $currency The 3-letter ISO 4217 currency code indicating the currency to use.
     * @return string
     * @throws Exception
     */
    public function currency(string $currency, bool $symbole = false): string {
        $bundle = \ResourceBundle::create($this->locale, 'ICUDATA-curr');
        return $this->get($bundle, 'Currencies')[$currency][$symbole ? 0 : 1];
    }

    /**
     * Translate a language identifier (e.g. En -> English, glk -> Gilaki)
     * If you have a locale identifier (en-Au) instead of language
     * use \Locale::getPrimaryLanguage($locale) to extract the language
     * @param $language
     * @return string
     * @throws Exception
     */
    public function language($language): string {
        $bundle = $this->getBundle('ICUDATA-lang')->get('Languages');
        return $this->get($bundle, strtolower($language));
    }

    /**
     * Translate a language identifier (e.g. En -> English, glk -> Gilaki)
     * If you have a locale identifier (en-Au) instead of language
     * use \Locale::getPrimaryLanguage($locale) to extract the language
     * @param string $country ISO 3166 country codes
     * @return string
     * @throws Exception
     */
    public function country(string $country): string {
        $bundle = $this->getBundle('ICUDATA-region')->get('Countries');
        return $this->get($bundle, strtoupper($country));
    }

    /**
     * Translate the scriptidentifier (e.g. 'zh_Hans' -> 'Simplified Chinese')
     * @param $script
     * @return string
     * @throws Exception
     */
    public function script(string $script): string {
        return $this->getBundle('ICUDATA-lang')->get('Scripts')->get(ucwords($script));
    }

    public function calendar(string $calendar): string {
        return $this->getBundle('ICUDATA-lang')->get('Types')->get('calendar')->get($calendar);
    }

    #endregion
    public function message(string $message, array $args): string {
        return \MessageFormatter::formatMessage($this->locale, $message, $args);
    }

    public function quote(string $quote): string {
        $bundle = $this->getBundle('ICUDATA')->get('delimiters');
        return $bundle->get('quotationStart') . $quote . $bundle->get('quotationEnd');
    }

    /**
     * Translate the calendar identifier (e.g. "buddhist" -> "Buddhist Calendar")
     * @param $calendar
     * @return string
     * @throws Exception
     */


    /**
     * @param float $value
     * @param string $currency The 3-letter ISO 4217 currency code indicating the currency to use.
     * @param string|null $pattern
     * @return string
     */
    public function money(float $value, string $currency, string $pattern = ''): string {
        return (new \NumberFormatter($this->locale, \NumberFormatter::CURRENCY, $pattern))->formatCurrency($value, $currency);
    }

    /**
     * @param float $value
     * @param int $precision
     * @return string
     */
    public function percentage(float $value, int $precision = 3): string {
        $formatter = new \NumberFormatter($this->locale, \NumberFormatter::PERCENT);
        $formatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $precision);
        return $formatter->format($value);
    }

    public function number(float $number): string {
        return (new \NumberFormatter($this->locale, \NumberFormatter::DEFAULT_STYLE))->format($number);
    }

    public function ordinal(int $number): string {
        return (new \NumberFormatter($this->locale, \NumberFormatter::ORDINAL))->format($number);
    }

    public function spellout(float $number): string {
        return (new \NumberFormatter($this->locale, \NumberFormatter::SPELLOUT))->format($number);
    }

    public function duration(float $duration): string {
        return $number = \NumberFormatter::create($this->locale, \NumberFormatter::DURATION)->format($duration);
    }

    /**
     * https://unicode-org.github.io/icu-docs/apidoc/released/icu4c/classSimpleDateFormat.html#details
     * @param $value
     * @param $format
     * @return false|string
     */
    public function customTime($value, string $format): string {
        $formatter = new \IntlDateFormatter($this->locale, null, null, $this->timezone, $this->calendarType, $format);
        return $formatter->format($value);
    }

    public function date($value, int $length = self::SHORT): string {
        return $this->datetime($value, $length, self::NONE);
    }

    public function datetime($value, int $datetype = self::SHORT, int $timetype = self::MEDIUM): string {
        $calendarType = $this->useLocalCalender ? \IntlDateFormatter::TRADITIONAL : \IntlDateFormatter::GREGORIAN;
        $formatter = new \IntlDateFormatter($this->locale, $datetype, $timetype, $this->timezone, $calendarType);
        return $formatter->format($value);
    }

    public function time($value, int $length = self::MEDIUM): string {
        return $this->datetime($value, self::NONE, $length);
    }

    private function verify($local): void {
        $local = preg_replace('#-#', '_', $local);
        if (!in_array($local, \ResourceBundle::getLocales(''))) {
            throw new Exception("Invalid locale $local");
        }
    }
}