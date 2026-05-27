<?php

declare(strict_types=1);

namespace Drago\Authorization\Control\Permissions;

use Drago;


/** Represents the data structure for the permissions roles view. */
class PermissionsRolesViewEntity extends Drago\Database\Entity
{
	public const string
		Table = 'permissions_roles_view',
		PrimaryKey = 'id',
		ColumnName = 'name',
		ColumnParent = 'parent';

	public int $id;
	public string $name;
	public int $parent;
}
