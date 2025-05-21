<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Access;

use Drago;


/**
 * Entity representing a user role view in the system.
 */
class AccessRolesViewEntity extends Drago\Database\Entity
{
	// Constants defining table and column names
	public const string
		Table = 'users_roles_view',
		ColumnUserId = 'user_id',
		ColumnUsername = 'username',
		ColumnRole = 'role';

	public ?int $user_id = null;
	public ?string $username = null;
	public string|array|null $role = null;
}
