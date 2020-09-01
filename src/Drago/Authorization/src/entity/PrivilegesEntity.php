<?php

declare(strict_types=1);

namespace Drago\Authorization\Entity;

final class PrivilegesEntity extends \Drago\Database\Entity
{
	use \Nette\SmartObject;

	const TABLE = 'privileges';
	const PRIVILEGE_ID = 'privilegeId';
	const NAME = 'name';

	public int $privilegeId;
	public string $name;
}
