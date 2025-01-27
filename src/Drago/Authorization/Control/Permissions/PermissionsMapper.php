<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Permissions;


/**
 * Trait used for mapping permissions data.
 * Contains the properties that define a permission in the system.
 */
trait PermissionsMapper
{
	/** ID of the permission (optional) */
	public ?int $id;

	/** Role ID associated with the permission */
	public int $role_id;

	/** Resource ID associated with the permission */
	public int $resource_id;

	/** Privilege ID associated with the permission */
	public int $privilege_id;

	/** Allowed status (0 = Denied, 1 = Allowed) */
	public int $allowed;
}
