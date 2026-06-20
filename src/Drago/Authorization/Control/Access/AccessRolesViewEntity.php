<?php

declare(strict_types=1);

namespace Drago\Authorization\Control\Access;

use Drago;


class AccessRolesViewEntity extends Drago\Database\Entity
{
	public const string
		Table = 'users_roles_view',
		ColumnUserId = 'user_id',
		ColumnUsername = 'username',
		ColumnRole = 'role';

	public ?int $user_id = null;
	public ?string $username = null;

	/** @var string|list<string>|null */
	public string|array|null $role = null;
}
