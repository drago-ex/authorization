<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Access;

use Drago;


/**
 * Entity class for user roles in the system.
 */
class AccessRolesEntity extends Drago\Database\Entity
{
	// Constant for the table name
	public const string Table = 'users_roles';

	// Constant for the column name representing the role ID
	public const string ColumnRoleId = 'role_id';

	// Constant for the column name representing the user ID
	public const string ColumnUserId = 'user_id';

	public int $role_id;
	public int $user_id;
}
