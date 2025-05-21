<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Privileges;

use Drago;


/**
 * This class represents a privilege entity mapped to the 'privileges' table.
 */
class PrivilegesEntity extends Drago\Database\Entity
{
	use PrivilegesMapper;

	// Constants defining table and column names
	public const string
		Table = 'privileges',
		PrimaryKey = 'id',
		ColumnName = 'name';
}
