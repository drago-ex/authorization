<?php

/**
 * This file was generated by Drago Generator.
 */

declare(strict_types=1);

namespace Drago\Authorization\Data;

use Drago;
use Nette;

class UsersRolesData extends Drago\Utils\ExtraArrayHash
{
	use Nette\SmartObject;

	public const ROLE_ID = 'role_id';
	public const USER_ID = 'user_id';
	public const EDIT_ID = 'edit_id';

	public array $role_id;
	public int $user_id;
	public int $edit_id;
}
