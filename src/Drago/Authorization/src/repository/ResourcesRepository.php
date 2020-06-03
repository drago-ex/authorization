<?php

declare(strict_types = 1);

/**
 * Drago Extension
 * Package built on Nette Framework
 */

namespace Drago\Authorization\Repository;

use Drago\Authorization\Entity\ResourcesEntity;
use Drago\Database;
use Dibi\Fluent;


class ResourcesRepository extends Database\Connect
{
	use Database\Repository;

	/** @var string */
	private $table = ResourcesEntity::TABLE;

	/** @var string */
	private $primaryId = ResourcesEntity::RESOURCE_ID;


	public function getAll(): Fluent
	{
		return $this->all()
			->orderBy('name', 'asc');
	}


	/**
	 * @return array|ResourcesEntity|null
	 * @throws \Dibi\Exception
	 */
	public function find(int $id)
	{
		return $this->discoverId($id)
			->setRowClass(ResourcesEntity::class)
			->fetch();
	}


	/**
	 * @throws \Dibi\Exception
	 */
	public function save(ResourcesEntity $entity): void
	{
		$id = $entity->getResourceId();
		$this->put($entity, $id);
	}
}
