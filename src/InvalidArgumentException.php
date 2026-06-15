<?php
/**
 * Created by Aiden Adrian
 */

declare(strict_types=1);

namespace Miloun\Cosmo;

/**
 * A caller passed an invalid argument — a typo'd option key, an unknown currency
 * code, an unsupported width/unit, a bad enum value, … A bug to fix in the calling
 * code, not a condition to catch and recover from.
 *
 * Extends {@see CosmoException}, so `catch (CosmoException)` catches all library errors.
 */
class InvalidArgumentException extends CosmoException
{
}
