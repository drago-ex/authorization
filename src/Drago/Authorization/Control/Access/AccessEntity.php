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
	/** The name of the database table */
	public const string Table = 'users';

	/** The primary key column name */
	public const string PrimaryKey = 'id';

	/** The column name for the username */
	public const string ColumnUsername = 'username';

	/** The user ID (nullable) */
	public ?int $id;

	/** The username of the user */
	public string $username;
}
