<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Access;

use Dibi\Connection;
use Dibi\Exception;
use Drago\Attr\AttributeDetectionException;
use Drago\Attr\Table;
use Drago\Database\Database;


/**
 * Repository for managing user roles in the system.
 * @extends Database<AccessRolesEntity>
 */
#[Table(AccessRolesEntity::Table, class: AccessRolesEntity::class)]
class AccessRolesRepository
{
	use Database;

	public function __construct(
		protected Connection $connection,
	) {
	}


	/**
	 * Fetch all roles for a specific user.
	 *
	 * @param int $userId User ID to fetch roles for.
	 * @return array[]|AccessRolesEntity[] List of roles associated with the user.
	 * @throws Exception If there is an issue with the database query.
	 * @throws AttributeDetectionException If attributes are not correctly detected.
	 */
	public function getUserRoles(int $userId): array
	{
		return $this->find(AccessRolesEntity::ColumnUserId, $userId)
			->recordAll();
	}
}
