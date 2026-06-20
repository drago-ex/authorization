<?php

declare(strict_types=1);

namespace Drago\Authorization\Control\Privileges;

use Drago;


class PrivilegesEntity extends Drago\Database\Entity
{
	use PrivilegesMapper;

	public const string
		Table = 'privileges',
		PrimaryKey = 'id',
		ColumnName = 'name';
}
