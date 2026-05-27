<?php

declare(strict_types=1);

namespace Drago\Authorization\Control\Resources;

use Drago;


class ResourcesEntity extends Drago\Database\Entity
{
	use ResourcesMapper;

	public const string
		Table = 'resources',
		PrimaryKey = 'id',
		ColumnName = 'name';
}
