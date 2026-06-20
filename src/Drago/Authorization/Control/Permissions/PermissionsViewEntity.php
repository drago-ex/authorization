<?php

declare(strict_types=1);

namespace Drago\Authorization\Control\Permissions;

use Drago;


class PermissionsViewEntity extends Drago\Database\Entity
{
	public const string
		Table = 'permissions_view',
		PrimaryKey = 'id',
		ColumnResource = 'resource',
		ColumnPrivilege = 'privilege',
		ColumnRole = 'role',
		ColumnAllowed = 'allowed';

	public int $id;
	public ?string $resource = null;
	public ?string $privilege = null;
	public ?string $role = null;
	public int $allowed;
}
