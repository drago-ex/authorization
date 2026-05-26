<?php

declare(strict_types=1);

namespace Drago\Authorization\Control\Privileges;

use Dibi\Connection;
use Drago\Attr\Table;
use Drago\Authorization\Conf;
use Drago\Authorization\NotAllowedChange;
use Drago\Database\Database;
use Drago\Database\ExtraFluent;


/** Repository class for managing Privileges entities. */
#[Table(PrivilegesEntity::Table, PrivilegesEntity::PrimaryKey, class: PrivilegesEntity::class)]
class PrivilegesRepository
{
	/** @use Database<PrivilegesEntity> */
	use Database;

	public function __construct(
		protected Connection $connection,
	) {
	}


	/** Returns all privileges, excluding the "all" privilege. */
	public function getAll(): ExtraFluent
	{
		return $this->read('*')
			->where(PrivilegesEntity::ColumnName, '!= ?', Conf::PrivilegeAll)
			->orderBy(PrivilegesEntity::ColumnName, 'asc');
	}


	/** Checks if the given privilege can be changed. */
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
