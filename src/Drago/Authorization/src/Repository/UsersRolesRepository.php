<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Repository;

use Dibi\Exception;
use Dibi\Result;
use Drago\Authorization\Entity\UsersRolesEntity;
use Drago\Database\Connect;
use Drago\Database\Repository;


class UsersRolesRepository extends Connect
{
	use Repository;

	public string $table = UsersRolesEntity::TABLE;
	public ?string $primary = null;


	/**
	 * @return array[]|UsersRolesEntity[]
	 * @throws Exception
	 */
	public function getAllUserRoles()
	{
		return $this->all()->execute()
			->setRowClass(UsersRolesEntity::class)
			->fetchAll();
	}


	/**
	 * @return array[]|UsersRolesEntity[]
	 * @throws Exception
	 */
	public function getUserRoles(int $userId)
	{
		return $this->all()
			->where(UsersRolesEntity::USER_ID, '= ?', $userId)->execute()
			->setRowClass(UsersRolesEntity::class)
			->fetchAll();
	}


	/**
	 * @throws Exception
	 */
	public function delete(UsersRolesEntity $entity): Result|int|null
	{
		return $this->db->delete($this->table)
			->where(UsersRolesEntity::USER_ID, '= ?', $entity->user_id)
			->and(UsersRolesEntity::ROLE_ID, '= ?', $entity->role_id)
			->execute();
	}


	/**
	 * @throws Exception
	 */
	public function getRecord(int $id): array|UsersRolesEntity|null
	{
		return $this->discover(UsersRolesEntity::USER_ID, $id)->execute()
			->setRowClass(UsersRolesEntity::class)
			->fetch();
	}
}
