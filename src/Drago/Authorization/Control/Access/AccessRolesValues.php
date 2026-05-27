<?php

declare(strict_types=1);

namespace Drago\Authorization\Control\Access;

use Drago;


/** Data class for Access roles. */
class AccessRolesValues extends Drago\Utils\ExtraArrayHash
{
	public const string Id = 'id';

	/** @var list<int> */
	public array $role_id;

	public ?int $user_id;
	public ?int $id;
}
