<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Permissions;

use Dibi\Connection;
use Dibi\Exception;
use Drago\Attr\AttributeDetectionException;
use Drago\Attr\Table;
use Drago\Database\Repository;
use Nette\SmartObject;


#[Table(PermissionsRolesViewEntity::table)]
class PermissionsRolesViewRepository
{
	use SmartObject;
	use Repository;

	public function __construct(
		protected Connection $db,
	) {
	}


	/**
	 * @return array[]|PermissionsRolesViewEntity[]
	 * @throws Exception
	 * @throws AttributeDetectionException
	 */
	public function getAll(): array
	{
		return $this->all()->execute()
			->setRowClass(PermissionsRolesViewEntity::class)
			->fetchAll();
	}
}
