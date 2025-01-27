<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Permissions;

use Dibi\Connection;
use Drago\Attr\Table;
use Drago\Database\Database;


/**
 * Repository for CRUD operations on PermissionsEntity.
 *
 * @extends Database<PermissionsEntity>
 */
#[Table(PermissionsEntity::Table, PermissionsEntity::PrimaryKey, class: PermissionsEntity::class)]
class PermissionsRepository
{
	use Database;

	public function __construct(
		protected Connection $connection,
	) {
	}
}
