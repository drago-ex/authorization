<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Access;

use Dibi\Connection;
use Dibi\Exception;
use Dibi\Result;
use Drago\Attr\AttributeDetectionException;
use Drago\Attr\Table;
use Drago\Database\Repository;


#[Table(AccessRolesEntity::Table)]
class AccessRolesRepository
{
	use Repository;

	public function __construct(
		protected Connection $db,
	) {
	}


	/**
	 * @return array[]|AccessRolesEntity[]
	 * @throws AttributeDetectionException
	 * @throws Exception
	 */
	public function getAllUserRoles(): array
	{
		return $this->all()->execute()
			->setRowClass(AccessRolesEntity::class)
			->fetchAll();
	}


	/**
	 * @return array[]|AccessRolesEntity[]
	 * @throws Exception
	 * @throws AttributeDetectionException
	 */
	public function getUserRoles(int $userId): array
	{
		return $this->all()
			->where(AccessRolesEntity::UserId, '= ?', $userId)
			->execute()->setRowClass(AccessRolesEntity::class)
			->fetchAll();
	}


	/**
	 * @throws Exception
	 */
	public function delete(AccessRolesEntity $entity): Result|int|null
	{
		return $this->db->delete(AccessRolesEntity::Table)
			->where(AccessRolesEntity::UserId, '= ?', $entity->user_id)
			->and(AccessRolesEntity::RoleId, '= ?', $entity->role_id)
			->execute();
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
	 * @throws Exception
	 * @throws AttributeDetectionException
	 */
	public function getRecord(int $id): array|AccessRolesEntity|null
	{
		return $this->discover(AccessRolesEntity::UserId, $id)
			->execute()->setRowClass(AccessRolesEntity::class)
			->fetch();
	}
}
