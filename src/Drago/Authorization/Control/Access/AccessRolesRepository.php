<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Access;

use Dibi\Connection;
use Dibi\Exception;
use Drago\Attr\AttributeDetectionException;
use Drago\Attr\Table;
use Drago\Database\Database;


/**
 * @extends Database<AccessRolesEntity>
 */
#[Table(AccessRolesEntity::Table, class: AccessRolesEntity::class)]
class AccessRolesRepository
{
	use Database;

	public function __construct(
		protected Connection $connection,
	) {
	}


	/**
	 * @return array[]|AccessRolesEntity[]
	 * @throws Exception
	 * @throws AttributeDetectionException
	 */
	public function getUserRoles(int $userId): array
	{
		return $this->find(AccessRolesEntity::ColumnUserId, $userId)
			->recordAll();
	}
}
