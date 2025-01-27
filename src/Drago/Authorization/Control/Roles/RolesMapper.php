<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Roles;


/**
 * Trait for mapping role data to the Role entity.
 * It defines the common properties used for a role's attributes.
 */
trait RolesMapper
{
	public ?int $id;
	public string $name;
	public string|int $parent;
}
