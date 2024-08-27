<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Permissions;

use Dibi\Connection;
use Dibi\Exception;
use Drago\Attr\AttributeDetectionException;
use Drago\Attr\Table;
use Drago\Authorization\Conf;
use Drago\Database\Database;
use Drago\Database\ExtraFluent;


/**
 * @extends Database<PermissionsViewEntity>
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
	 * @throws AttributeDetectionException
	 */
	public function getAll(): ExtraFluent
	{
		return $this->read('*')
			->where(PermissionsViewEntity::ColumnRole, '!= ?', Conf::RoleAdmin);
	}
}
