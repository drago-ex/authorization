<?php

declare(strict_types = 1);

/**
 * Drago Extension
 * Package built on Nette Framework
 */

namespace Drago\Authorization\Repository;

use Drago\Authorization\Authorizator;
use Drago\Authorization\Entity\RolesEntity;
use Drago\Database;


class RolesRepository extends Database\Connect
{
	use Database\Repository;

	/** @var string */
	private $table = RolesEntity::TABLE;

	/** @var string */
	private $primaryId = RolesEntity::ROLE_ID;


	/**
	 * @return array[]|RolesEntity[]
	 * @throws \Dibi\Exception
	 */
	public function getAll()
	{
		return $this->all()->execute()
			->setRowClass(RolesEntity::class)
			->fetchAll();
	}


	/**
	 * @return array|RolesEntity|null
	 * @throws \Dibi\Exception
	 */
	public function find(int $id)
	{
		return $this->discoverId($id)
			->setRowClass(RolesEntity::class)
			->fetch();
	}


	/**
	 * @return array|\Dibi\Row|null
	 * @throws \Dibi\Exception
	 * @throws \Exception
	 */
	public function findParent(int $id)
	{
		$row = $this->discover(RolesEntity::PARENT, $id)->fetch();
		if ($row) {
			throw new \Exception('The record can not be deleted, you must first delete the
			records that are associated with it.', 0002);
		}
		return $row;
	}


	/**
	 * @throws \Exception
	 */
	public function isAllowed(RolesEntity $row): bool
	{
		$role = $row->getName();
		if ($role === Authorizator::ROLE_GUEST || $role === Authorizator::ROLE_MEMBER || $role === Authorizator::ROLE_ADMIN) {
			throw new \Exception('The role is not allowed to be edited or deleted.', 0003);
		}
		return true;
	}


	/**
	 * @throws \Dibi\Exception
	 */
	public function save(RolesEntity $entity): void
	{
		$id = $entity->getRoleId();
		$this->put($entity, $id);
	}
}
