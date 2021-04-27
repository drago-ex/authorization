<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Repository;

use Dibi\Exception;
use Drago\Authorization\Entity\PermissionsEntity;
use Drago\Database\Connect;
use Drago\Database\Repository;


class PermissionsRepository extends Connect
{
	use Repository;

	public string $table = PermissionsEntity::TABLE;
	public string $primary = PermissionsEntity::PRIMARY;


	/**
	 * @throws Exception
	 */
	public function getRecord(int $id): array|PermissionsEntity|null
	{
		return $this->get($id)->execute()
			->setRowClass(PermissionsEntity::class)
			->fetch();
	}
}
