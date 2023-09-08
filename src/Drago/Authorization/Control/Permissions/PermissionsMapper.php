<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Permissions;


trait PermissionsMapper
{
	public ?int $id;
	public int $role_id;
	public int $resource_id;
	public int $privilege_id;
	public int $allowed;
}
