<?php

declare(strict_types=1);

namespace Drago\Authorization\Control\Access;

use Dibi\Connection;
use Drago\Attr\Table;
use Drago\Database\Database;


/** Repository for managing user roles in the system. */
#[Table(AccessRolesEntity::Table, class: AccessRolesEntity::class)]
class AccessRolesRepository
{
	/** @use Database<AccessRolesEntity> */
	use Database;

	public function __construct(
		protected Connection $connection,
	) {
	}


	/**
	 * Fetch all roles for a specific user.
	 * @return list<AccessRolesEntity>
	 */
	public function getUserRoles(int $userId): array
	{
		return $this->find(AccessRolesEntity::ColumnUserId, $userId)
			->recordAll();
	}
}
