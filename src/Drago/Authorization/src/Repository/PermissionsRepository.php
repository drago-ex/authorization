<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Repository;

use Dibi\Exception;
use Drago\Attr\Table;
use Drago\Authorization\Entity\PermissionsEntity;
use Drago\Database\Connect;
use Drago\Database\Repository;


#[Table(PermissionsEntity::TABLE, PermissionsEntity::PRIMARY)]
class PermissionsRepository extends Connect
{
	/**
	 * @throws Exception
	 */
	public function getRecord(int $id): array|PermissionsEntity|null
	{
		return $this->get($id)->fetch();
	}
}
