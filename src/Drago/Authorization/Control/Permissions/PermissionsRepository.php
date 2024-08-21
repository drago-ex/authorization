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


#[From(PermissionsEntity::Table, PermissionsEntity::PrimaryKey, class: PermissionsEntity::class)]
class PermissionsRepository extends Database
{
	/**
	 * @throws Exception
	 * @throws AttributeDetectionException
	 */
	public function getOne(int $id): PermissionsEntity|null
	{
		return $this->find(PermissionsEntity::PrimaryKey, $id)
			->record();
	}
}
