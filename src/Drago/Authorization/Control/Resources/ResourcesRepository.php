<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Resources;

use Dibi\Connection;
use Dibi\Exception;
use Dibi\Fluent;
use Dibi\Result;
use Drago\Attr\AttributeDetectionException;
use Drago\Attr\Table;
use Drago\Database\Repository;


#[Table(ResourcesEntity::Table, ResourcesEntity::Id)]
class ResourcesRepository
{
	use Repository;

	public function __construct(
		protected Connection $db,
	) {
	}


	/**
	 * @throws AttributeDetectionException
	 */
	public function getAll(): Fluent
	{
		return $this->all()
			->orderBy(ResourcesEntity::Name, 'asc');
	}


	/**
	 * @return array[]|ResourcesEntity[]
	 * @throws AttributeDetectionException
	 * @throws Exception
	 */
	public function getAllResources(): array
	{
		return $this->getAll()->execute()
			->setRowClass(ResourcesEntity::class)
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
	 * @throws AttributeDetectionException
	 */
	public function save(ResourcesData $data): Result|int|null
	{
		return $this->put($data->toArray());
	}
}
