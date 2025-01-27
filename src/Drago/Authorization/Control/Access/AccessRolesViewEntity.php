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
	// Table name in the database
	public const string Table = 'users_roles_view';

	// Column names in the table
	public const string ColumnUserId = 'user_id';
	public const string ColumnUsername = 'username';
	public const string ColumnRole = 'role';

	public ?int $user_id = null;
	public ?string $username = null;
	public string|array|null $role = null;
}
