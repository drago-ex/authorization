<?php

/**
 * This file was generated by Drago Generator.
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Permissions;

use Drago;


class PermissionsData extends Drago\Utils\ExtraArrayHash
{
	public const ID = 'id';
	public const ROLE_ID = 'role_id';
	public const RESOURCE_ID = 'resource_id';
	public const PRIVILEGE_ID = 'privilege_id';
	public const ALLOWED = 'allowed';

	public ?int $id;
	public int $role_id;
	public int $resource_id;
	public int $privilege_id;
	public int $allowed;
}
