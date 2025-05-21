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
use Drago\Authorization\Control\Privileges\PrivilegesEntity;
use Drago\Database\Database;
use Drago\Database\ExtraFluent;


#[Table(PermissionsEntity::TABLE, PermissionsEntity::PRIMARY)]
class PermissionsRepository
{
	use Database;

	public function __construct(
		protected Connection $connection,
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
	 * @throws AttributeDetectionException
	 * @throws Exception
	 */
	public function remove(int $id): Result|int
	{
		return $this->delete(PermissionsEntity::PRIMARY, $id)
			->execute();
	}
}
