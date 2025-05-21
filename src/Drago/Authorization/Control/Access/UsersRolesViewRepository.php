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
use Drago\Database\Database;


#[Table(UsersRolesViewEntity::TABLE)]
class UsersRolesViewRepository
{
	use Database;

	public function __construct(
		protected Connection $connection,
	) {
	}


	/**
	 * @throws AttributeDetectionException
	 */
	public function getAllUsers(): Fluent
	{
		return $this->read('user_id, username, LISTAGG(NVL(description, role), ", ") WITHIN GROUP (order by description asc) role')
			->groupBy('user_id, username')
			->orderBy(UsersRolesViewEntity::USER_ID, 'asc');
	}


	/**
	 * @return array[]|UsersRolesViewEntity[]
	 * @throws Exception
	 * @throws AttributeDetectionException
	 */
	public function getAllUsersRoles(): array
	{
		return $this->read('*')
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
		return $this->read('*')->execute()
			->setRowClass(UsersRolesViewEntity::class)
			->fetchAll();
	}
}
