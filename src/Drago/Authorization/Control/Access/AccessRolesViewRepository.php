<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Access;

use Dibi\Fluent;
use Drago\Attr\AttributeDetectionException;
use Drago\Attr\From;
use Drago\Authorization\Conf;
use Drago\Database\Database;


#[From(AccessRolesViewEntity::Table)]
class AccessRolesViewRepository extends Database
{
	/**
	 * @throws AttributeDetectionException
	 */
	public function getAllUsers(): Fluent
	{
		return $this->getConnection()
			->select('user_id, username, group_concat(role separator ", ") role')
			->from($this->getTableName())->groupBy('user_id, username')
			->having('sum(case when role = ? then 1 else 0 end) = ?', Conf::RoleAdmin, 0)
			->orderBy(AccessRolesViewEntity::ColumnUserId, 'asc');
	}
}
