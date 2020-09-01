<?php

declare(strict_types = 1);

/**
 * Drago AclExtension
 * Package built on Nette Framework
 */

namespace Drago\Authorization\Repository;

use Drago\Authorization\Auth;
use Drago\Authorization\Entity\PrivilegesEntity;
use Drago\Database\Connect;
use Drago\Database\Repository;


class PrivilegesRepository extends Connect
{
	use Repository;

	public string $table = PrivilegesEntity::TABLE;
	public string $columnId = PrivilegesEntity::PRIVILEGE_ID;


	/**
	 * @throws \Exception
	 */
	public function isAllowed(string $privilege): bool
	{
		if ($privilege === Auth::PRIVILEGE_ALL) {
			throw new \Exception('The record is not allowed to be edited or deleted.', 1001);
		}
		return true;
	}
}
