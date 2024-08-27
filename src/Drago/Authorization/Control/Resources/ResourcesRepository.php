<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Resources;

use Dibi\Connection;
use Dibi\Exception;
use Drago\Attr\AttributeDetectionException;
use Drago\Attr\Table;
use Drago\Database\Database;
use Drago\Database\ExtraFluent;


/**
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
	 * @throws AttributeDetectionException
	 */
	public function getAll(): ExtraFluent
	{
		return $this->read('*')
			->orderBy(ResourcesEntity::ColumnName, 'asc');
	}
}
