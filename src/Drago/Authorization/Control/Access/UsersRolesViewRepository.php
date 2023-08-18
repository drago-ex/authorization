<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Access;

use Dibi\Connection;
use Dibi\Exception;
use Dibi\Fluent;
use Drago\Attr\AttributeDetectionException;
use Drago\Attr\Table;
use Drago\Authorization\Conf;
use Drago\Database\Repository;
use Nette\SmartObject;


#[Table(UsersRolesViewEntity::TABLE)]
class UsersRolesViewRepository
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
	public function getAllUsers(): Fluent
	{
		return $this->db
			->select('user_id, username, LISTAGG(role, ", ") WITHIN GROUP (order by role asc) role')
			->from($this->getTable())->groupBy('user_id, username')
			->having('SUM(CASE WHEN role = ? THEN 1 ELSE 0 END) = ?', Conf::ROLE_ADMIN, 0)
			->orderBy(UsersRolesViewEntity::USER_ID, 'asc');
	}


	/**
	 * @return array[]|UsersRolesViewEntity[]
	 * @throws Exception
	 * @throws AttributeDetectionException
	 */
	public function getAllUsersRoles(): array
	{
		return $this->all()
			->orderBy(UsersRolesViewEntity::USER_ID, 'asc')
			->execute()->setRowClass(UsersRolesViewEntity::class)
			->fetchAll();
	}


	/**
	 * @return array[]|UsersRolesViewEntity[]
	 * @throws Exception
	 * @throws AttributeDetectionException
	 */
	public function getUserRoles(): array
	{
		return $this->all()->execute()
			->setRowClass(UsersRolesViewEntity::class)
			->fetchAll();
	}
}
