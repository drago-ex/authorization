<?php

declare(strict_types = 1);

/**
 * Drago Extension
 * Package built on Nette Framework
 */

namespace Drago\Authorization\Repository;

use Dibi\Fluent;
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
	 * @throws \Dibi\Exception
	 */
	public function save(Entity\PrivilegesEntity $entity): void
	{
		$id = $entity->getPrivilegeId();
		$this->put($entity, $id);
	}
}
