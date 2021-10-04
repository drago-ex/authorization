<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Repository;

use Dibi\Exception;
use Dibi\Result;
use Dibi\Row;
use Drago\Attr\Table;
use Drago\Authorization\Entity\UsersRolesEntity;
use Drago\Database\Connect;


#[Table(UsersRolesEntity::TABLE)]
class UsersRolesRepository extends Connect
{
	/**
	 * @return array[]|UsersRolesEntity[]
	 * @throws Exception
	 */
	public function getAllUserRoles()
	{
		return $this->all()->fetchAll();
	}


	/**
	 * @return array[]|UsersRolesEntity[]
	 * @throws Exception
	 */
	public function getUserRoles(int $userId)
	{
		return $this->all()
			->where(UsersRolesEntity::USER_ID, '= ?', $userId)
			->fetchAll();
	}


	/**
	 * @throws Exception
	 */
	public function delete(UsersRolesEntity $entity): Result|int|null
	{
		return $this->db->delete($this->getTable())
			->where(UsersRolesEntity::USER_ID, '= ?', $entity->user_id)
			->and(UsersRolesEntity::ROLE_ID, '= ?', $entity->role_id)
			->execute();
	}


	/**
	 * @throws Exception
	 */
	public function getRecord(int $id): array|UsersRolesEntity|Row|null
	{
		return $this->discover(UsersRolesEntity::USER_ID, $id)->fetch();
	}
}
