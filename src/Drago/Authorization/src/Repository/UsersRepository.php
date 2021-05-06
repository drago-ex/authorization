<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Repository;

use App\Entity\AccessEntity;
use Drago\Database\Connect;
use Drago\Database\Repository;


class UsersRepository extends Connect
{
	use Repository;

	public string $table = AccessEntity::TABLE;
	public string $primary = AccessEntity::PRIMARY;


	public function getAllUsers(): array
	{
		return $this->all()
			->fetchPairs(AccessEntity::PRIMARY, AccessEntity::USERNAME);
	}
}
