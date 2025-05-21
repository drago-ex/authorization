<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Resources;

use Drago;


class ResourcesEntity extends Drago\Database\Entity
{
	use ResourcesMapper;

	// Constants defining table and column names
	public const string
		Table = 'resources',
		PrimaryKey = 'id',
		ColumnName = 'name';
}
