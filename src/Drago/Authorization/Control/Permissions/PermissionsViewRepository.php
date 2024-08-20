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
use Drago\Authorization\Conf;
use Drago\Database\Database;
use Drago\Database\FluentExtra;


#[From(PermissionsViewEntity::Table, class: PermissionsViewEntity::class)]
class PermissionsViewRepository extends Database
{
	/**
	 * @throws AttributeDetectionException
	 */
	public function getAll(): FluentExtra
	{
		return $this->read()
			->where(PermissionsViewEntity::Role, '!= ?', Conf::RoleAdmin);
	}


	/**
	 * @return array[]|PermissionsViewEntity[]
	 * @throws AttributeDetectionException
	 * @throws Exception
	 */
	public function getAllPermissions(): array
	{
		return $this->getAll()
			->recordAll();
	}
}
