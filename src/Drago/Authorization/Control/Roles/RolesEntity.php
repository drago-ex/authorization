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

	// Define table and column names as constants
	public const string Table = 'roles';
	public const string PrimaryKey = 'id';
	public const string ColumnName = 'name';
	public const string ColumnParent = 'parent';
}
