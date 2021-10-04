<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Repository;

use Dibi\Exception;
use Drago\Attr\Table;
use Drago\Authorization\Entity\PermissionsViewEntity;
use Drago\Database\Connect;
use Drago\Database\Repository;


#[Table(PermissionsViewEntity::TABLE)]
class PermissionsViewRepository extends Connect
{
	/**
	 * @return array[]|PermissionsViewEntity[]
	 * @throws Exception
	 */
	public function getAll()
	{
		return $this->all()->fetchAll();
	}
}
