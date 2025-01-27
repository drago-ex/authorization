<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Permissions;

use Drago;


/**
 * Data class for permissions.
 * Extends ExtraArrayHash to map permissions data.
 */
class PermissionsData extends Drago\Utils\ExtraArrayHash
{
	// Trait for mapping permissions-related data
	use PermissionsMapper;
}
