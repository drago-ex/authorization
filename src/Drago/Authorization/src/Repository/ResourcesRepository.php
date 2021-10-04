<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */


declare(strict_types=1);

namespace Drago\Authorization\Repository;

use Dibi\Exception;
use Drago\Attr\Table;
use Drago\Authorization\Entity\ResourcesEntity;
use Drago\Database\Connect;


#[Table(ResourcesEntity::TABLE, ResourcesEntity::PRIMARY)]
class ResourcesRepository extends Connect
{
	/**
	 * @return array[]|ResourcesEntity[]
	 * @throws Exception
	 */
	public function getAll()
	{
		return $this->all()
			->orderBy(ResourcesEntity::NAME, 'asc')
			->fetchAll();
	}


	/**
	 * @throws Exception
	 */
	public function getRecord(int $id): array|ResourcesEntity|null
	{
		return $this->get($id)->fetch();
	}
}
