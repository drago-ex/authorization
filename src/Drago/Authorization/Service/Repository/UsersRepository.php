<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Service\Repository;

use Drago\Authorization\Service\Entity\AccessEntity;
use Drago\Attr\Table;
use Drago\Database\Connect;


#[Table(AccessEntity::TABLE, AccessEntity::PRIMARY)]
class UsersRepository extends Connect
{
	public function getAllUsers(): array
	{
		return $this->all()
			->fetchPairs(AccessEntity::PRIMARY, AccessEntity::USERNAME);
	}
}
