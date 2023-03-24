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
use Nette\SmartObject;


#[Table(AccessEntity::TABLE, AccessEntity::PRIMARY)]
class UsersRepository
{
	use SmartObject;
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
		return $this->db->select('u.id, u.username')->from($this->getTable())->as('u')
			->leftJoin(UsersRolesViewEntity::TABLE)->as('r')->on('u.id = r.user_id')
			->groupBy('u.id, u.username')
			->having('SUM(CASE WHEN r.role = ? THEN 1 ELSE 0 END) = ?', Conf::ROLE_ADMIN, 0)
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
