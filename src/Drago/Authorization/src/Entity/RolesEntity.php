<?php

/**
 * This file was generated by Drago Generator.
 */

declare(strict_types=1);

namespace Drago\Authorization\Entity;

use Drago;
use Nette;

class RolesEntity extends Drago\Database\Entity
{
	use Nette\SmartObject;

	public const TABLE = 'roles';
	public const PRIMARY = 'id';
	public const NAME = 'name';
	public const PARENT = 'parent';

	public int $id;
	public string $name;
	public string|int $parent;
}
