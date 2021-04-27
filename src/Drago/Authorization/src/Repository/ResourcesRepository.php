<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */


declare(strict_types=1);

namespace Drago\Authorization\Repository;

use Dibi\Exception;
use Drago\Authorization\Entity\ResourcesEntity;
use Drago\Database\Connect;
use Drago\Database\Repository;


class ResourcesRepository extends Connect
{
	use Repository;

	public string $table = ResourcesEntity::TABLE;
	public string $primary = ResourcesEntity::PRIMARY;


	/**
	 * @return array[]|ResourcesEntity[]
	 * @throws Exception
	 */
	public function getAll()
	{
		return $this->all()
			->orderBy(ResourcesEntity::NAME, 'asc')->execute()
			->setRowClass(ResourcesEntity::class)
			->fetchAll();
	}


	/**
	 * @throws Exception
	 */
	public function getRecord(int $id): array|ResourcesEntity|null
	{
		return $this->get($id)->execute()
			->setRowClass(ResourcesEntity::class)
			->fetch();
	}
}
