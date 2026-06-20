<?php

declare(strict_types=1);

namespace Drago\Authorization\Control\Roles;

use Drago\Database\Entity;


class RolesEntity extends Entity
{
	use RolesMapper;

	public const string
		Table = 'roles',
		PrimaryKey = 'id',
		ColumnName = 'name',
		ColumnParent = 'parent';
}
