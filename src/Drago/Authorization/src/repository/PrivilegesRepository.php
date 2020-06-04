<?php

declare(strict_types = 1);

/**
 * Drago Extension
 * Package built on Nette Framework
 */

namespace Drago\Authorization\Repository;

use Dibi\Fluent;
use Drago\Authorization\Authorizator;
use Drago\Authorization\Entity;
use Drago\Database;


class PrivilegesRepository extends Database\Connect
{
	use Database\Repository;

	/** @var string */
	private $table = Entity\PrivilegesEntity::TABLE;

	/** @var string */
	private $primaryId = Entity\PrivilegesEntity::PRIVILEGE_ID;


	public function getAll(): Fluent
	{
		return $this->all()
			->orderBy('name', 'asc');
	}


	/**
	 * @return array|Entity\PrivilegesEntity|null
	 * @throws \Dibi\Exception
	 */
	public function find(int $id)
	{
		return $this->discoverId($id)
			->setRowClass(Entity\PrivilegesEntity::class)
			->fetch();
	}


	/**
	 * @throws \Exception
	 */
	public function isAllowed(Entity\PrivilegesEntity $row): bool
	{
		$role = $row->getName();
		if ($role === Authorizator::PRIVILEGE_ALL) {
			throw new \Exception('The privilege is not allowed to be edited or deleted.', 0003);
		}
		return true;
	}


	/**
	 * @throws \Dibi\Exception
	 */
	public function save(Entity\PrivilegesEntity $entity): void
	{
		$id = $entity->getPrivilegeId();
		$this->put($entity, $id);
	}
}
