<?php

declare(strict_types=1);

namespace Drago\Authorization\Control\Roles;


trait RolesMapper
{
	public ?int $id;
	public string $name;
	public int $parent;
}
