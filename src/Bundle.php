<?php
/**
 * Created by Aiden Adrian
 */
declare(strict_types=1);

namespace Salarmehr\Cosmopolitan;

use ReturnTypeWillChange;

class Bundle extends \ResourceBundle
{

    const string BRKITR = 'ICUDATA-brkitr'; // Break Iterator Rule Source Data
    const string CURRENCY = 'ICUDATA-curr';
    const string LOCALE = 'ICUDATA';
    const string LANGUAGE = 'ICUDATA-lang';

    /**
     * @inheritDoc
     * @throws Exception
     */
    #[ReturnTypeWillChange] #[\Override]
    public function get($index, $fallback = null): mixed
    {
        $output = parent::get($index);
        if (intl_is_failure(self::getErrorCode())) {
            throw new Exception(self::getErrorMessage(), self::getErrorCode());
        }
        return $output;
    }

    /**
     * Returns whether a resource bundle exists for the given locale.
     * @param string $locale BCP 47 locale identifier.
     * @return bool
     */
    public static function hasBundle(string $locale): bool
    {
        return in_array(\Locale::canonicalize($locale), self::getLocales(''));
    }
}