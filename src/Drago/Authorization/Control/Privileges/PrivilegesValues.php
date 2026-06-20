<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Privileges;

use Drago;


class PrivilegesValues extends Drago\Utils\ExtraArrayHash
{
	use PrivilegesMapper;
}
