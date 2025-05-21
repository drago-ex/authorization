<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Access;

use Drago;


/**
 * Represents an entity for user access data.
 */
class AccessEntity extends Drago\Database\Entity
{
	// Constants defining table and column names
	public const string
		Table = 'users',
		PrimaryKey = 'id',
		ColumnUsername = 'username';

	/** The user ID (nullable) */
	public ?int $id;

	/** The username of the user */
	public string $username;
}
