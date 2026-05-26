<?php

declare(strict_types=1);

namespace Drago\Authorization\Control\Privileges;


/** Trait for mapping privilege properties. */
trait PrivilegesMapper
{
	public ?int $id;
	public string $name;
}
