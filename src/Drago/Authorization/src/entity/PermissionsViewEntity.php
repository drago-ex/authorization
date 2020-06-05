<?php

declare(strict_types=1);

namespace Drago\Authorization\Entity;

/**
 * @property int $permissionId
 * @property string $allowed
 * @property string $resources
 * @property string $privilege
 * @property string $role
 */
class PermissionsViewEntity extends \Drago\Database\Entity
{
	public const TABLE = 'permissions_view';
	public const PERMISSION_ID = 'permissionId';
	public const ALLOWED = 'allowed';
	public const RESOURCES = 'resources';
	public const PRIVILEGE = 'privilege';
	public const ROLE = 'role';

	/** @var int */
	public $permissionId;

	/** @var string */
	public $allowed;

	/** @var string */
	public $resources;

	/** @var string */
	public $privilege;

	/** @var string */
	public $role;


	public function getPermissionId(): int
	{
		return $this->permissionId;
	}


	public function getAllowed(): string
	{
		return $this->allowed;
	}


	public function getResources(): ?string
	{
		return $this->resources;
	}


	public function getPrivilege(): ?string
	{
		return $this->privilege;
	}


	public function getRole(): ?string
	{
		return $this->role;
	}
}
