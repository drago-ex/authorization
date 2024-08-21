<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Privileges;

use Dibi\Exception;
use Drago\Attr\AttributeDetectionException;
use Drago\Attr\From;
use Drago\Authorization\Conf;
use Drago\Authorization\NotAllowedChange;
use Drago\Database\Database;
use Drago\Database\FluentExtra;


#[From(PrivilegesEntity::Table, PrivilegesEntity::PrimaryKey, class: PrivilegesEntity::class)]
class PrivilegesRepository extends Database
{
	/**
	 * @throws AttributeDetectionException
	 */
	public function getAll(): FluentExtra
	{
		return $this->read()
			->where(PrivilegesEntity::ColumnName, '!= ?', Conf::PrivilegeAll)
			->orderBy(PrivilegesEntity::ColumnName, 'asc');
	}


	/**
	 * @throws Exception
	 * @throws AttributeDetectionException
	 */
	public function getOne(int $id): PrivilegesEntity|null
	{
		return $this->find(PrivilegesEntity::PrimaryKey, $id)
			->record();
	}


	/**
	 * @throws NotAllowedChange
	 */
	public function isAllowed(string $privilege): bool
	{
		if ($privilege === Conf::PrivilegeAll) {
			throw new NotAllowedChange(
				'The record is not allowed to be edited or deleted.',
				1001,
			);
		}
		return true;
	}
}
