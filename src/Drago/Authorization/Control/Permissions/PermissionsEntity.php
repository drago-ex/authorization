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

	// Constants defining table and column names for permissions
	public const string Table = 'permissions';
	public const string PrimaryKey = 'id';
	public const string ColumnRoleId = 'role_id';
	public const string ColumnResourceId = 'resource_id';
	public const string ColumnPrivilegeId = 'privilege_id';
	public const string ColumnAllowed = 'allowed';
}
