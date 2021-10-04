<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Repository;

use Dibi\Exception;
use Drago\Attr\Table;
use Drago\Authorization\Entity\UsersRolesViewEntity;
use Drago\Database\Connect;

#[Table(UsersRolesViewEntity::TABLE)]
class UsersRolesViewRepository extends Connect
{
	/**
	 * @return array[]|UsersRolesViewEntity[]
	 * @throws Exception
	 */
	public function getAllUsersRoles()
	{
		return $this->all()
			->orderBy(UsersRolesViewEntity::USER_ID, 'asc')
			->fetchAll();
	}


	public function getUserRoles(): array
	{
		return $this->all()->fetchAll();
	}
}
