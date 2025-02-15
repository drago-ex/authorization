<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Permissions;

use Drago;


/**
 * Represents the data structure for the permissions roles view.
 * Contains role information such as ID, name, and parent role.
 */
class PermissionsRolesViewEntity extends Drago\Database\Entity
{
	// Table name in the database
	public const string Table = 'permissions_roles_view';

	// Column names in the table
	public const string PrimaryKey = 'id';
	public const string ColumnName = 'name';
	public const string ColumnParent = 'parent';

	/** Role ID */
	public int $id;

	/** Role name */
	public string $name;

	/** Parent role ID */
	public int $parent;
}
