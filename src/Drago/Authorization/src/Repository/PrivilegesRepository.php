<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Repository;

use Drago\Authorization\Conf;
use Drago\Authorization\Entity\PrivilegesEntity;
use Drago\Authorization\NotAllowedChange;
use Drago\Database\Connect;
use Drago\Database\Repository;


class PrivilegesRepository extends Connect
{
	use Repository;

	public string $table = PrivilegesEntity::TABLE;
	public string $primary = PrivilegesEntity::PRIMARY;


	/**
	 * @throws \Dibi\Exception
	 */
	public function getRecord(int $id): array|PrivilegesEntity|null
	{
		return $this->get($id)->execute()
			->setRowClass(PrivilegesEntity::class)
			->fetch();
	}


	/**
	 * @throws NotAllowedChange
	 */
	public function isAllowed(string $privilege): bool
	{
		if ($privilege === Conf::PRIVILEGE_ALL) {
			throw new NotAllowedChange('The record is not allowed to be edited or deleted.', 1001);
		}
		return true;
	}
}
