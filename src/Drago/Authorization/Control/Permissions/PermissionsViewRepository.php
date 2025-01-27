<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Permissions;

use Dibi\Connection;
use Drago\Attr\AttributeDetectionException;
use Drago\Attr\Table;
use Drago\Authorization\Conf;
use Drago\Database\Database;
use Drago\Database\ExtraFluent;


/**
 * Repository for handling the 'permissions_view' table.
 * Provides methods for retrieving permissions, excluding admin roles.
 */
#[Table(PermissionsViewEntity::Table, class: PermissionsViewEntity::class)]
class PermissionsViewRepository
{
	use Database;

	public function __construct(
		protected Connection $connection,
	) {
	}


	/**
	 * Retrieves all permissions from the database excluding admin roles.
	 *
	 * @throws AttributeDetectionException If attributes are incorrectly detected
	 * @return ExtraFluent Fluent query builder for fetching the data
	 */
	public function getAll(): ExtraFluent
	{
		return $this->read('*')
			->where(PermissionsViewEntity::ColumnRole, '!= ?', Conf::RoleAdmin);
	}
}
