<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Repository;

use Dibi\Exception;
use Drago\Authorization\Entity\PermissionsViewEntity;
use Drago\Database\Connect;
use Drago\Database\Repository;


class PermissionsViewRepository extends Connect
{
	use Repository;

	public string $table = PermissionsViewEntity::TABLE;


	/**
	 * @return array[]|PermissionsViewEntity[]
	 * @throws Exception
	 */
	public function getAll()
	{
		return $this->all()->execute()
			->setRowClass(PermissionsViewEntity::class)
			->fetchAll();
	}
}
