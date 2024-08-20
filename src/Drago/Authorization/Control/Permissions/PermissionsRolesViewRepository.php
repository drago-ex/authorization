<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Permissions;

use Dibi\Exception;
use Drago\Attr\AttributeDetectionException;
use Drago\Attr\From;
use Drago\Database\Database;


#[From(PermissionsRolesViewEntity::Table, class: PermissionsRolesViewEntity::class)]
class PermissionsRolesViewRepository extends Database
{
	/**
	 * @return array[]|PermissionsRolesViewEntity[]
	 * @throws Exception
	 * @throws AttributeDetectionException
	 */
	public function getAll(): array
	{
		return $this->read()
			->recordAll();
	}
}
