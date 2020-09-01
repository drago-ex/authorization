<?php

declare(strict_types=1);

namespace Drago\Authorization\Entity;

final class ResourcesEntity extends \Drago\Database\Entity
{
	use \Nette\SmartObject;

	const TABLE = 'resources';
	const RESOURCE_ID = 'resourceId';
	const NAME = 'name';

	public int $resourceId;
	public string $name;
}
