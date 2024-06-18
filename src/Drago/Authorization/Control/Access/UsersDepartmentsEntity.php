<?php

/**
 * This file was generated by Drago Generator.
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Access;

use Drago;


class UsersDepartmentsEntity extends Drago\Database\EntityOracle
{
	public const TABLE = 'USERS_DEPARTMENTS';
	public const PRIMARY = 'ID';
	public const USERNAME = 'USER_ID';
	public const EMAIL = 'DEPARTMENT_ID';

	public ?int $id;
	public int $user_id;
	public int $department_id;
}
