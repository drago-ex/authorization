<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Resources;

use Dibi\Connection;
use Dibi\Exception;
use Dibi\Result;
use Drago\Attr\AttributeDetectionException;
use Drago\Attr\Table;
use Drago\Database\Repository;


#[Table(ResourcesEntity::TABLE, ResourcesEntity::PRIMARY)]
class ResourcesRepository
{
	use Repository;

	public function __construct(
		protected Connection $db,
	) {
	}


	/**
	 * @return ResourcesEntity[]
	 * @throws Exception
	 * @throws AttributeDetectionException
	 */
	public function getAll(): array
	{
		return $this->all()
			->orderBy(ResourcesEntity::NAME, 'asc')
			->execute()->setRowClass(ResourcesEntity::class)
			->fetchAll();
	}


	/**
	 * @throws AttributeDetectionException
	 * @throws Exception
	 */
	public function getOne(int $id): array|ResourcesEntity|null
	{
		return $this->get($id)->execute()
			->setRowClass(ResourcesEntity::class)
			->fetch();
	}


	/**
	 * @throws Exception
	 */
	public function save(ResourcesData $data): Result|int|null
	{
		return $this->db->insert(ResourcesEntity::TABLE, $data->toArray())
			->execute();
	}
}
