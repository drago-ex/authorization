<?php declare(strict_types=1);

/**
 * Drago Extension
 * Package built on Nette Framework
 */

namespace Drago\Authorization\Control\Permissions;

use Drago;


class PermissionsValues extends Drago\Utils\ExtraArrayHash
{
	use PermissionsMapper;
}
