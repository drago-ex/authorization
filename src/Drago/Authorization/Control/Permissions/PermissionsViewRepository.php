<?php

declare(strict_types=1);

namespace Drago\Authorization\Control\Permissions;

use Dibi\Connection;
use Drago\Attr\AttributeDetectionException;
use Drago\Attr\Table;
use Drago\Authorization\Conf;
use Drago\Database\Database;
use Drago\Database\ExtraFluent;


/** Repository for handling the 'permissions_view' table. */
#[Table(PermissionsViewEntity::Table, class: PermissionsViewEntity::class)]
class PermissionsViewRepository
{
	/** @phpstan-use Database<PermissionsViewEntity> */
	use Database;

	public function __construct(
		protected Connection $connection,
	) {
	}


	/**
	 * Retrieves all permissions from the database excluding admin roles.
	 * @return ExtraFluent<PermissionsViewEntity>
	 * @throws AttributeDetectionException
	 */
	public function getAll(): ExtraFluent
	{
		return $this->read('*')
			->where(PermissionsViewEntity::ColumnRole, '!= ?', Conf::RoleAdmin);
	}
}
