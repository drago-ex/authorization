<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Resources;

use Dibi\Exception;
use Drago\Attr\AttributeDetectionException;
use Drago\Attr\From;
use Drago\Database\Database;
use Drago\Database\FluentExtra;


#[From(ResourcesEntity::Table, ResourcesEntity::Id, class: ResourcesEntity::class)]
class ResourcesRepository extends Database
{
	/**
	 * @throws AttributeDetectionException
	 */
	public function getAll(): FluentExtra
	{
		return $this->read()
			->orderBy(ResourcesEntity::Name, 'asc');
	}


	/**
	 * @return array[]|ResourcesEntity[]
	 * @throws AttributeDetectionException
	 * @throws Exception
	 */
	public function getAllResources(): array
	{
		return $this->read()
			->recordAll();
	}


	/**
	 * @throws AttributeDetectionException
	 * @throws Exception
	 */
	public function getOne(int $id): array|ResourcesEntity|null
	{
		return $this->find(ResourcesEntity::Id, $id)
			->record();
	}
}
