<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Access;

use Dibi\Connection;
use Dibi\Fluent;
use Drago\Attr\AttributeDetectionException;
use Drago\Attr\Table;
use Drago\Authorization\Conf;
use Drago\Database\Database;


/**
 * Repository for accessing the users' roles view.
 * Provides methods to fetch user roles and related data from the database.
 */
#[Table(AccessRolesViewEntity::Table, class: AccessRolesViewEntity::class)]
class AccessRolesViewRepository
{
	use Database;

	public function __construct(
		protected Connection $connection,
	) {
	}


	/**
	 * Fetches all users, their usernames, and the roles associated with them.
	 *
	 * @throws AttributeDetectionException
	 */
	public function getAllUsers(): Fluent
	{
		return $this->getConnection()
			->select('user_id, username, group_concat(role separator ", ") role')
			->from($this->getTableName())
			->groupBy('user_id, username')
			->having('sum(case when role = ? then 1 else 0 end) = ?', Conf::RoleAdmin, 0)
			->orderBy(AccessRolesViewEntity::ColumnUserId, 'asc');
	}
}
