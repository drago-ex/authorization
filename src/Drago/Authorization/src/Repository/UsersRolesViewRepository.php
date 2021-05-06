<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Repository;

use Dibi\Exception;
use Drago\Authorization\Entity\UsersRolesViewEntity;
use Drago\Database\Connect;
use Drago\Database\Repository;


class UsersRolesViewRepository extends Connect
{
	use Repository;

	public string $table = UsersRolesViewEntity::TABLE;


	/**
	 * @return array[]|UsersRolesViewEntity[]
	 * @throws Exception
	 */
	public function getAllUsersRoles()
	{
		return $this->all()
			->orderBy(UsersRolesViewEntity::USER_ID, 'asc')->execute()
			->setRowClass(UsersRolesViewEntity::class)
			->fetchAll();
	}


	public function getUserRoles(): array
	{
		return $this->all()->execute()
			->setRowClass(UsersRolesViewEntity::class)
			->fetchAll();
	}
}
