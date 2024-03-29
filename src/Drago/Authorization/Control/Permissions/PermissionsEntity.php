<?php

/**
 * This file was generated by Drago Generator.
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Permissions;

use Drago;


class PermissionsEntity extends Drago\Database\Entity
{
	use PermissionsMapper;

	public const Table = 'permissions';
	public const Id = 'id';
	public const RoleId = 'role_id';
	public const ResourceId = 'resource_id';
	public const PrivilegeId = 'privilege_id';
	public const Allowed = 'allowed';
}
