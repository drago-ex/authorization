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


#[Table(AccessEntity::table, AccessEntity::id)]
class AccessRepository
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
			->leftJoin(AccessRolesViewEntity::table)->as('r')->on('u.id = r.user_id')
			->groupBy('u.id, u.username')
			->having('sum(case when r.role = ? then 1 else 0 end) = ?', Conf::roleAdmin, 0)
			->fetchPairs(AccessEntity::id, AccessEntity::username);
	}


	/**
	 * @throws AttributeDetectionException
	 */
	public function getUserById(int $id): array|Row|null
	{
		return $this->get($id)
			->fetchPairs(AccessEntity::id, AccessEntity::username);
	}
}
