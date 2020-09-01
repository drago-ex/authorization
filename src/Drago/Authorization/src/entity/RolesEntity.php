<?php

declare(strict_types=1);

namespace Drago\Authorization\Entity;

final class RolesEntity extends \Drago\Database\Entity
{
	use \Nette\SmartObject;

	const TABLE = 'roles';
	const ROLE_ID = 'roleId';
	const NAME = 'name';
	const PARENT = 'parent';

	public int $roleId;
	public string $name;
	public int $parent;
	public ?string $parentName;
}
