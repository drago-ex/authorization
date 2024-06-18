<?php

/**
 * This file was generated by Drago Generator.
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Access;

use Drago;


class UsersRolesData extends Drago\Utils\ExtraArrayHash
{
	public const ROLE_ID = 'role_id';
	public const USER_ID = 'user_id';
	public const ID = 'id';
	public const DEPARTMENT_ID = 'department_id';

	public array $role_id;
	public ?int $user_id;
	public ?int $id;
	public array $department_id;
}
