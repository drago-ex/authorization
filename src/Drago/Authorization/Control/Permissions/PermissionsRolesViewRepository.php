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
use Drago\Database\Database;


#[Table(PermissionsRolesViewEntity::Table, class: PermissionsRolesViewEntity::class)]
class PermissionsRolesViewRepository
{
	use Database;

	public function __construct(
		protected Connection $connection,
	) {
	}


	/**
	 * @return array[]|PermissionsRolesViewEntity[]
	 * @throws Exception
	 * @throws AttributeDetectionException
	 */
	public function getAll(): array
	{
		return $this->read()
			->recordAll();
	}
}
