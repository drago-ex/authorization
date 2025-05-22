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
use Drago\Database\Database;


#[Table(UsersRolesEntity::TABLE)]
class UsersRolesRepository
{
	use Database;

	public function __construct(
		protected Connection $connection,
	) {
	}


	/**
	 * @return array[]|UsersRolesEntity[]
	 * @throws AttributeDetectionException
	 * @throws Exception
	 */
	public function getAllUserRoles(): array
	{
		return $this->read('*')->execute()
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
		return $this->read('*')
			->where(UsersRolesEntity::USER_ID, '= ?', $userId)
			->execute()->setRowClass(UsersRolesEntity::class)
			->fetchAll();
	}


	/**
	 * @throws Exception
	 * @throws AttributeDetectionException
	 */
	public function deleteRole(UsersRolesEntity $entity): Result|int|null
	{
		return $this->delete(UsersRolesEntity::USER_ID, $entity->user_id)
			->and(UsersRolesEntity::ROLE_ID, '= ?', $entity->role_id)
			->execute();
	}


	/**
	 * @throws Exception
	 * @throws AttributeDetectionException
	 */
	public function deleteByUserId(int $userId): Result|int|null
	{
		return $this->delete(UsersRolesEntity::USER_ID, $userId)
			->execute();
	}


	/**
	 * @throws Exception
	 */
	public function insert(UsersRolesEntity $entity): Result|int|null
	{
		return $this->connection->insert(UsersRolesEntity::TABLE, $entity->toArrayUpper())
			->execute();
	}


	/**
	 * @throws Exception
	 * @throws AttributeDetectionException
	 */
	public function getRecord(int $id): array|UsersRolesEntity|null
	{
		return $this->find(UsersRolesEntity::USER_ID, $id)
			->execute()->setRowClass(UsersRolesEntity::class)
			->fetch();
	}


	public function getDb(): Connection
	{
		return $this->connection;
	}
}
