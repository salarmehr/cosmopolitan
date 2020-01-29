<?php
/**
 * Created by Reza Salarmehr.
 */

declare(strict_types=1);

namespace Salarmehr\Cosmopolitan;

class Bundle extends \ResourceBundle
{
    public function get($index, $fallback = null)
    {
        $output = parent::get($index, $fallback);
        if (intl_is_failure(self::getErrorCode())) {
            throw new Exception(parent::getErrorMessage());
        }
        return $output;
    }
}