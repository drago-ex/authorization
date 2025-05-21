<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Roles;

use Drago\Database\Entity;


/**
 * Class representing a role entity, which maps to the 'roles' table in the database.
 * It defines the primary key and column names used for mapping the data.
 */
class RolesEntity extends Entity
{
	use RolesMapper;

	// Constants defining table and column names
	public const string
		Table = 'roles',
		PrimaryKey = 'id',
		ColumnName = 'name',
		ColumnParent = 'parent';
}
