<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Resources;

use Dibi\Connection;
use Drago\Attr\AttributeDetectionException;
use Drago\Attr\Table;
use Drago\Database\Database;
use Drago\Database\ExtraFluent;


/**
 * Repository for accessing resources in the database.
 * @extends Database<ResourcesEntity>
 */
#[Table(ResourcesEntity::Table, ResourcesEntity::PrimaryKey, class: ResourcesEntity::class)]
class ResourcesRepository
{
	use Database;

	public function __construct(
		protected Connection $connection,
	) {
	}


	/**
	 * Retrieves all resources from the database.
	 *
	 * @throws AttributeDetectionException
	 * @return ExtraFluent
	 */
	public function getAll(): ExtraFluent
	{
		return $this->read('*')
			->orderBy(ResourcesEntity::ColumnName, 'asc');
	}
}
