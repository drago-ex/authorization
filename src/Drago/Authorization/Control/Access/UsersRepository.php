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
use Drago\Database\Database;


#[Table(AccessEntity::TABLE, AccessEntity::PRIMARY)]
class UsersRepository
{
	use Database;

	public function __construct(
		protected Connection $db,
	) {
	}


	/**
	 * @throws AttributeDetectionException
	 */
	public function getAllUsers(): array
	{
		return $this->read('id, username')
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
