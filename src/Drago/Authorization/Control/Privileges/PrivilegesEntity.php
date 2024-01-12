<?php

/**
 * This file was generated by Drago Generator.
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Privileges;

use Drago;


class PrivilegesEntity extends Drago\Database\EntityOracle
{
	public const TABLE = 'ACL_PRIVILEGES';
	public const PRIMARY = 'ID';
	public const NAME = 'NAME';

	public ?int $id;
	public string $name;
}
