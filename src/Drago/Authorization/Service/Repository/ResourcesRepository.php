<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */


declare(strict_types=1);

namespace Drago\Authorization\Service\Repository;

use Dibi\Exception;
use Dibi\Row;
use Drago\Attr\Table;
use Drago\Authorization\Service\Entity\ResourcesEntity;
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
	public function getRecord(int $id): array|ResourcesEntity|Row|null
	{
		return $this->get($id)->fetch();
	}
}
