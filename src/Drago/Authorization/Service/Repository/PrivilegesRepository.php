<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Service\Repository;

use Dibi\Exception;
use Dibi\Row;
use Drago\Attr\Table;
use Drago\Authorization\Conf;
use Drago\Authorization\Service\Entity\PrivilegesEntity;
use Drago\Authorization\NotAllowedChange;
use Drago\Database\Connect;


#[Table(PrivilegesEntity::TABLE, PrivilegesEntity::PRIMARY)]
class PrivilegesRepository extends Connect
{
	/**
	 * @throws Exception
	 */
	public function getRecord(int $id): array|PrivilegesEntity|Row|null
	{
		return $this->get($id)->fetch();
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
