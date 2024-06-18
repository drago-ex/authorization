<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Access;

use Dibi\Connection;
use Dibi\Row;
use Drago\Attr\AttributeDetectionException;
use Drago\Attr\Table;
use Drago\Authorization\Conf;
use Drago\Database\Repository;


#[Table(AccessEntity::TABLE, AccessEntity::PRIMARY)]
class UsersRepository
{
	use Repository;

	public function __construct(
		protected Connection $db,
	) {
	}


	/**
	 * @throws AttributeDetectionException
	 */
	public function getAllUsers(): array
	{
		return $this->db->select('id, username')->from($this->getTable())
			->fetchPairs(AccessEntity::PRIMARY, AccessEntity::USERNAME);
	}


	/**
	 * @throws AttributeDetectionException
	 */
	public function getUserById(int $id): array|Row|null
	{
		return $this->get($id)
			->fetchPairs(AccessEntity::PRIMARY, AccessEntity::USERNAME);
	}
}
