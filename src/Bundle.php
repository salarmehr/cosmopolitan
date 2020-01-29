<?php
/**
 * Created by Reza Salarmehr.
 */
declare(strict_types=1);

namespace Salarmehr\Cosmopolitan;

class Bundle extends \ResourceBundle
{

    const BRKITR = 'ICUDATA-brkitr'; // Break Iterator Rule Source Data
    const CURRENCY = 'ICUDATA-curr';
    const LOCALE = 'ICUDATA';
    const LANGUAGE = 'ICUDATA-lang';

    /**
     * @inheritDoc
     */
    public function get($index, $fallback = null)
    {
        $output = parent::get($index);
        if (intl_is_failure(self::getErrorCode())) {
            throw new Exception(self::getErrorMessage());
        }
        return $output;
    }

    /**
     * Check if there is a bundle associated to a locale
     * @param string $local
     * @return bool
     */
    public static function hasBundle(string $local): bool
    {
        return in_array(\Locale::canonicalize($local), self::getLocales(''));
    }
}