<?php

declare(strict_types = 1);

/**
 * Drago AclExtension
 * Package built on Nette Framework
 */

namespace Drago\Authorization\Repository;

use Drago\Authorization\Entity\ResourcesEntity;
use Drago\Database\Connect;
use Drago\Database\Repository;


class ResourcesRepository extends Connect
{
	use Repository;

	public string $table = ResourcesEntity::TABLE;
	public string $columnId = ResourcesEntity::RESOURCE_ID;
}
