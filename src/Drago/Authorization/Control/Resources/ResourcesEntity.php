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

	public const string Table = 'resources';
	public const string PrimaryKey = 'id';
	public const string ColumnName = 'name';
}
