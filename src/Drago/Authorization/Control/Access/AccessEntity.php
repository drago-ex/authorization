<?php

declare(strict_types=1);

namespace Drago\Authorization\Control\Access;

use Drago;


/** Represents an entity for user access data. */
class AccessEntity extends Drago\Database\Entity
{
	public const string
		Table = 'users',
		PrimaryKey = 'id',
		ColumnUsername = 'username';

	public ?int $id;
	public string $username;
}
