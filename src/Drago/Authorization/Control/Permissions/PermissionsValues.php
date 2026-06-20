<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Permissions;

use Drago;


class PermissionsValues extends Drago\Utils\ExtraArrayHash
{
	use PermissionsMapper;
}
