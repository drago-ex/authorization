<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Access;

use Dibi\Connection;
use Dibi\Exception;
use Drago\Attr\AttributeDetectionException;
use Drago\Attr\Table;
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
	 * @return UsersRolesViewEntity[]
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
	 * @return UsersRolesViewEntity[]
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
