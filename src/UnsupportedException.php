<?php
/**
 * Created by Aiden Adrian
 */

declare(strict_types=1);

namespace Miloun\Cosmo;

/**
 * The requested operation has no binding in PHP's intl surface (e.g. enumerating
 * currencies via supportedValues). Environmental, not a caller bug.
 *
 * Extends {@see CosmoException}, so `catch (CosmoException)` catches all library errors.
 */
class UnsupportedException extends CosmoException
{
}
