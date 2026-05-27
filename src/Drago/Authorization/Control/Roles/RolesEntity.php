<?php

declare(strict_types=1);

namespace Drago\Authorization\Control\Roles;

use Drago\Database\Entity;


/** Class representing a role entity, which maps to the 'roles' table in the database. */
class RolesEntity extends Entity
{
	use RolesMapper;

	public const string
		Table = 'roles',
		PrimaryKey = 'id',
		ColumnName = 'name',
		ColumnParent = 'parent';
}
