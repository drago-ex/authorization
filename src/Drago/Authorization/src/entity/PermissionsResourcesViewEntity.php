<?php

declare(strict_types=1);

namespace Drago\Authorization\Entity;

/**
 * @property string $allowed
 * @property string $resource
 * @property string $role
 */
class PermissionsResourcesViewEntity extends \Drago\Database\Entity
{
	public const TABLE = 'permissions_resources_view';
	public const ALLOWED = 'allowed';
	public const RESOURCE = 'resource';
	public const ROLE = 'role';

	/** @var string */
	public $allowed;

	/** @var string */
	public $resource;

	/** @var string */
	public $role;


	public function getAllowed(): string
	{
		return $this->allowed;
	}


	public function getResource(): ?string
	{
		return $this->resource;
	}


	public function getRole(): ?string
	{
		return $this->role;
	}
}
