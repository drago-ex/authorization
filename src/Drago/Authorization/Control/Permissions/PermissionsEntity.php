<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Permissions;

use Drago;


/**
 * Entity class for managing permission records.
 * Extends Drago\Database\Entity to handle database operations.
 */
class PermissionsEntity extends Drago\Database\Entity
{
	// Trait for mapping permissions-related data
	use PermissionsMapper;

	// Constants defining table and column names
	public const string
		Table = 'permissions',
		PrimaryKey = 'id',
		ColumnRoleId = 'role_id',
		ColumnResourceId = 'resource_id',
		ColumnPrivilegeId = 'privilege_id',
		ColumnAllowed = 'allowed';
}
