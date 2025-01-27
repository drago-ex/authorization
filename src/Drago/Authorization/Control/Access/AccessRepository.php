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
use Drago\Database\Database;


/**
 * Repository for accessing user-related data.
 */
#[Table(AccessEntity::Table, AccessEntity::PrimaryKey)]
class AccessRepository
{
	use Database;

	public function __construct(
		protected Connection $connection,
	) {
	}


	/**
	 * Fetch all users excluding admin role.
	 * @throws AttributeDetectionException
	 */
	public function getAllUsers(): array
	{
		return $this->getConnection()
			->select('u.id, u.username')
			->from($this->getTableName())->as('u')
			->leftJoin(AccessRolesViewEntity::Table)->as('r')->on('u.id = r.user_id')
			->groupBy('u.id, u.username')
			->having('sum(case when r.role = ? then 1 else 0 end) = ?', Conf::RoleAdmin, 0)
			->fetchPairs(AccessEntity::PrimaryKey, AccessEntity::ColumnUsername);
	}


	/**
	 * Fetch a user by their ID.
	 * @throws AttributeDetectionException
	 */
	public function getUserById(int $id): array|Row|null
	{
		return $this->get($id)->fetchPairs(
			AccessEntity::PrimaryKey,
			AccessEntity::ColumnUsername,
		);
	}
}
