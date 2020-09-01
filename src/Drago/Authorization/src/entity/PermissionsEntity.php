<?php

declare(strict_types=1);

namespace Drago\Authorization\Entity;

final class PermissionsEntity extends \Drago\Database\Entity
{
	use \Nette\SmartObject;

	const TABLE = 'permissions';
	const PERMISSION_ID = 'permissionId';
	const ROLE_ID = 'roleId';
	const RESOURCE_ID = 'resourceId';
	const PRIVILEGE_ID = 'privilegeId';
	const ALLOWED = 'allowed';

	public int $permissionId;
	public int $roleId;
	public int $resourceId;
	public int $privilegeId;
	public string $allowed;
}
