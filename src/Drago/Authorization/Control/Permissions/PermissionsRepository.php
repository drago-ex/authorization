<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Permissions;

use Dibi\Connection;
use Dibi\Exception;
use Dibi\Result;
use Drago\Attr\AttributeDetectionException;
use Drago\Attr\Table;
use Drago\Database\Repository;
use Nette\SmartObject;


#[Table(PermissionsEntity::TABLE, PermissionsEntity::PRIMARY)]
class PermissionsRepository
{
	use SmartObject;
	use Repository;

	public function __construct(
		protected Connection $db,
	) {
	}


	/**
	 * @throws Exception
	 * @throws AttributeDetectionException
	 */
	public function getOne(int $id): array|PermissionsEntity|null
	{
		return $this->get($id)->execute()
			->setRowClass(PermissionsEntity::class)
			->fetch();
	}


	/**
	 * @throws Exception
	 * @throws AttributeDetectionException
	 */
	public function save(PermissionsData $data): Result|int|null
	{
		return $this->put($data->toArray());
	}
}
