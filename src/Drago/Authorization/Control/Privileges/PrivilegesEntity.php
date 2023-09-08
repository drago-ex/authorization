<?php

/**
 * This file was generated by Drago Generator.
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Privileges;

use Drago;
use Nette;


class PrivilegesEntity extends Drago\Database\Entity
{
	use Nette\SmartObject;
	use PrivilegesMapper;

	public const table = 'privileges';
	public const id = 'id';
	public const name = 'name';
}
