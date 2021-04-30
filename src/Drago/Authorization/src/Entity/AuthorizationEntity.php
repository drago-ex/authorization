<?php

/**
 * This file was generated by Drago Generator.
 */

declare(strict_types=1);

namespace App\Entity;

use Drago;
use Nette;

class AuthorizationEntity extends Drago\Database\EntityOracle
{
	use Nette\SmartObject;

	public const TABLE = 'authorization';
	public const ROLE_ID = 'role_id';
	public const USER_ID = 'user_id';

	public int $role_id;
	public int $user_id;
}
