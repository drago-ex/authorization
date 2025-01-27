<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization;

use Exception;


/**
 * Exception thrown when a file is not found.
 *
 * This exception is thrown when a requested file cannot be found in the expected location.
 */
class FileNotFoundException extends Exception
{
}
