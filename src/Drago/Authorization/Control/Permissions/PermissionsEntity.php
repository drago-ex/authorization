<?php

declare(strict_types=1);

namespace Drago\Authorization\Control\Permissions;

use Drago;


/** Entity class for managing permission records. */
class PermissionsEntity extends Drago\Database\Entity
{
	use PermissionsMapper;

	public const string
		Table = 'permissions',
		PrimaryKey = 'id',
		ColumnRoleId = 'role_id',
		ColumnResourceId = 'resource_id',
		ColumnPrivilegeId = 'privilege_id',
		ColumnAllowed = 'allowed';
}
