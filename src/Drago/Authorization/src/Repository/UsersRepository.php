<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Repository;

use App\Entity\AccessEntity;
use Drago\Attr\Table;
use Drago\Database\Connect;
use Drago\Database\Repository;


#[Table(AccessEntity::TABLE, AccessEntity::PRIMARY)]
class UsersRepository extends Connect
{
	public function getAllUsers(): array
	{
		return $this->all()
			->fetchPairs(AccessEntity::PRIMARY, AccessEntity::USERNAME);
	}
}
