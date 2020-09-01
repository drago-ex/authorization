<?php

declare(strict_types = 1);

/**
 * Drago AclExtension
 * Package built on Nette Framework
 */

namespace Drago\Authorization\Repository;

use Drago\Authorization\Entity\RolesEntity;
use Drago\Authorization\Auth;
use Drago\Database\Connect;
use Drago\Database\Repository;


class RolesRepository extends Connect
{
	use Repository;

	public string $table = RolesEntity::TABLE;
	public string $columnId = RolesEntity::ROLE_ID;


	/**
	 * @throws \Exception
	 */
	public function isAllowed(string $role): bool
	{
		if ($role === Auth::ROLE_GUEST || $role === Auth::ROLE_MEMBER || $role === Auth::ROLE_ADMIN) {
			throw new \Exception('The record is not allowed to be edited or deleted.', 1001);
		}
		return true;
	}


	/**
	 * @return array|RolesEntity|null
	 * @throws \Dibi\Exception
	 * @throws \Exception
	 */
	public function findParent(int $id)
	{
		$row = $this->discover(RolesEntity::PARENT, $id)->fetch();
		if ($row) {
			throw new \Exception('The record can not be deleted, 
			you must first delete the records that are associated with it.', 1002);
		}
		return $row;
	}
}
