<?php

declare(strict_types=1);

namespace Drago\Authorization\Entity;

/**
 * @property int $id
 * @property int $roleId
 * @property int $resourceId
 * @property int $privilegeId
 * @property string $allowed
 */
class PermissionsEntity extends \Drago\Database\Entity
{
	public const TABLE = 'permissions';
	public const ID = 'id';
	public const ROLE_ID = 'roleId';
	public const RESOURCE_ID = 'resourceId';
	public const PRIVILEGE_ID = 'privilegeId';
	public const ALLOWED = 'allowed';

	/** @var int */
	public $id;

	/** @var int */
	public $roleId;

	/** @var int */
	public $resourceId;

	/** @var int */
	public $privilegeId;

	/** @var string */
	public $allowed;


	public function getId(): ?int
	{
		return $this->id;
	}


	public function setId(int $var)
	{
		$this['id'] = $var;
	}


	public function getRoleId(): int
	{
		return $this->roleId;
	}


	public function setRoleId(int $var)
	{
		$this['roleId'] = $var;
	}


	public function getResourceId(): int
	{
		return $this->resourceId;
	}


	public function setResourceId(int $var)
	{
		$this['resourceId'] = $var;
	}


	public function getPrivilegeId(): int
	{
		return $this->privilegeId;
	}


	public function setPrivilegeId(int $var)
	{
		$this['privilegeId'] = $var;
	}


	public function getAllowed(): string
	{
		return $this->allowed;
	}


	public function setAllowed(string $var)
	{
		$this['allowed'] = $var;
	}
}
