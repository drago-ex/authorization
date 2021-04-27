<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Repository;

use Drago\Authorization\Entity\PermissionsRolesViewEntity;
use Drago\Database\Connect;
use Drago\Database\Repository;


class PermissionsRolesViewRepository extends Connect
{
	use Repository;

	public string $table = PermissionsRolesViewEntity::TABLE;
}
