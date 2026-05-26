<?php

declare(strict_types=1);

namespace Drago\Authorization\Control\Permissions;

use Dibi\Connection;
use Drago\Attr\Table;
use Drago\Database\Database;


/** Repository for retrieving data from the 'permissions_roles_view' table. */
#[Table(PermissionsRolesViewEntity::Table, class: PermissionsRolesViewEntity::class)]
class PermissionsRolesViewRepository
{
	/** @use Database<PermissionsRolesViewEntity> */
	use Database;

	public function __construct(
		protected Connection $connection,
	) {
	}


	/**
	 * Fetches all records from the 'permissions_roles_view' table.
	 * @return list<PermissionsRolesViewEntity>
	 */
	public function getAll(): array
	{
		return $this->read('*')
			->recordAll();
	}
}
