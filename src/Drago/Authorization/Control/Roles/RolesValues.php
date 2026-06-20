<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Roles;

use Drago;


class RolesValues extends Drago\Utils\ExtraArrayHash
{
	use RolesMapper;
}
