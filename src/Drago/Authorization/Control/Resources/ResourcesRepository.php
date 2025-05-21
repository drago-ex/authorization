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
use Drago\Database\Database;


#[Table(ResourcesEntity::TABLE, ResourcesEntity::PRIMARY)]
class ResourcesRepository
{
	use Database;

	public function __construct(
		protected Connection $connection,
	) {
	}


	/**
	 * @throws AttributeDetectionException
	 */
	public function getAll(): Fluent
	{
		return $this->read('*')
			->orderBy(ResourcesEntity::NAME, 'asc');
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
	public function remove(int $id): Result|int|null
	{
		return $this->delete(ResourcesEntity::PRIMARY, $id)
			->execute();
	}
}
