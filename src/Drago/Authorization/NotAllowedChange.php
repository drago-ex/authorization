<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization;

use Exception;

/**
 * Exception thrown when an operation is not allowed to change.
 *
 * This exception is thrown when a user attempts to change something they are not authorized to modify.
 */
class NotAllowedChange extends Exception
{
}
