<?php

declare(strict_types=1);

namespace Drago\Authorization\Entity;

/**
 * @property int $permissionId
 * @property string $allowed
 * @property string $resources
 * @property string $privileges
 * @property string $roles
 */
class PermissionsPrivilegesViewEntity extends \Drago\Database\Entity
{
	public const TABLE = 'permissions_privileges_view';
	public const PERMISSION_ID = 'permissionId';
	public const ALLOWED = 'allowed';
	public const RESOURCES = 'resources';
	public const PRIVILEGES = 'privileges';
	public const ROLES = 'roles';

	/** @var int */
	public $permissionId;

	/** @var string */
	public $allowed;

	/** @var string */
	public $resources;

	/** @var string */
	public $privileges;

	/** @var string */
	public $roles;


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


	public function getPrivileges(): ?string
	{
		return $this->privileges;
	}


	public function getRoles(): ?string
	{
		return $this->roles;
	}
}
