<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Roles;

use Drago;


/**
 * Represents role data, extending ExtraArrayHash and utilizing the RolesMapper for mapping role attributes.
 */
class RolesData extends Drago\Utils\ExtraArrayHash
{
	use RolesMapper;
}
