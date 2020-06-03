<?php

declare(strict_types=1);

namespace Drago\Authorization\Entity;

/**
 * @property int $resourceId
 * @property string $name
 */
class ResourcesEntity extends \Drago\Database\Entity
{
	public const TABLE = 'resources';
	public const RESOURCE_ID = 'resourceId';
	public const NAME = 'name';

	/** @var int */
	public $resourceId;

	/** @var string */
	public $name;


	public function getResourceId(): ?int
	{
		return $this->resourceId;
	}


	public function setResourceId(int $var)
	{
		$this['resourceId'] = $var;
	}


	public function getName(): string
	{
		return $this->name;
	}


	public function setName(string $var)
	{
		$this['name'] = $var;
	}
}
