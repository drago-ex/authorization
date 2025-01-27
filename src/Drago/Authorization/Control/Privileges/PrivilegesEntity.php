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

	public const string Table = 'privileges';
	public const string PrimaryKey = 'id';
	public const string ColumnName = 'name';
}
