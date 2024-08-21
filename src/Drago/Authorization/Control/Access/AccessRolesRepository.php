<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Access;

use Dibi\Exception;
use Dibi\Result;
use Drago\Attr\AttributeDetectionException;
use Drago\Attr\From;
use Drago\Database\Database;


#[From(AccessRolesEntity::Table, class: AccessRolesEntity::class)]
class AccessRolesRepository extends Database
{
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


	/**
	 * @throws Exception
	 */
	public function insert(AccessRolesEntity $entity): Result|int|null
	{
		return $this->db->insert(AccessRolesEntity::Table, $entity->toArray())
			->execute();
	}


	/**
	 * @throws AttributeDetectionException
	 */
	public function getRecord(int $id): array|AccessRolesEntity|null
	{
		return $this->find(AccessRolesEntity::ColumnUserId, $id)
			->fetch();
	}
}
