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
use Nette\SmartObject;


#[Table(UsersRolesEntity::TABLE)]
class UsersRolesRepository
{
	use SmartObject;
	use Repository;

	public function __construct(
		protected Connection $db,
	) {
	}


	/**
	 * @return array[]|UsersRolesEntity[]
	 * @throws AttributeDetectionException
	 * @throws Exception
	 */
	public function getAllUserRoles(): array
	{
		return $this->all()->execute()
			->setRowClass(UsersRolesEntity::class)
			->fetchAll();
	}


	/**
	 * @return array[]|UsersRolesEntity[]
	 * @throws Exception
	 * @throws AttributeDetectionException
	 */
	public function getUserRoles(int $userId): array
	{
		return $this->all()
			->where(UsersRolesEntity::USER_ID, '= ?', $userId)
			->execute()->setRowClass(UsersRolesEntity::class)
			->fetchAll();
	}


	/**
	 * @throws Exception
	 */
	public function delete(UsersRolesEntity $entity): Result|int|null
	{
		return $this->db->delete(UsersRolesEntity::TABLE)
			->where(UsersRolesEntity::USER_ID, '= ?', $entity->user_id)
			->and(UsersRolesEntity::ROLE_ID, '= ?', $entity->role_id)
			->execute();
	}


	/**
	 * @throws Exception
	 */
	public function insert(UsersRolesEntity $entity): Result|int|null
	{
		return $this->db->insert(UsersRolesEntity::TABLE, $entity->toArray())
			->execute();
	}


	/**
	 * @throws Exception
	 * @throws AttributeDetectionException
	 */
	public function getRecord(int $id): array|UsersRolesEntity|null
	{
		return $this->discover(UsersRolesEntity::USER_ID, $id)
			->execute()->setRowClass(UsersRolesEntity::class)
			->fetch();
	}
}
