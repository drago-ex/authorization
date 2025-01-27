<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Privileges;


/**
 * This trait maps the properties of the Privileges entity.
 */
trait PrivilegesMapper
{
	public ?int $id;
	public string $name;
}
