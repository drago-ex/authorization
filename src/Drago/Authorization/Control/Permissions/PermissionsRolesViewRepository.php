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
use Drago\Database\Database;


/**
 * Repository for retrieving data from the 'permissions_roles_view' table.
 */
#[Table(PermissionsRolesViewEntity::Table, class: PermissionsRolesViewEntity::class)]
class PermissionsRolesViewRepository
{
	use Database;

	public function __construct(
		protected Connection $connection,
	) {
	}


	/**
	 * Fetches all records from the 'permissions_roles_view' table.
	 *
	 * @return array[]|PermissionsRolesViewEntity[] Array of all roles as entities
	 * @throws Exception If there's a database error
	 * @throws AttributeDetectionException If attributes are not correctly detected
	 */
	public function getAll(): array
	{
		return $this->read('*')
			->recordAll();
	}
}
