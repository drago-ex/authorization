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
	// Constants defining table and column names
	public const string
		Table = 'users_roles',
		ColumnRoleId = 'role_id',
		ColumnUserId = 'user_id';

	public int $role_id;
	public int $user_id;
}
