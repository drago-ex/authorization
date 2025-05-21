<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Permissions;

use Dibi\Connection;
use Dibi\Exception;
use Dibi\Fluent;
use Drago\Attr\AttributeDetectionException;
use Drago\Attr\Table;
use Drago\Authorization\Conf;
use Drago\Database\Database;


#[Table(PermissionsViewEntity::TABLE)]
class PermissionsViewRepository
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
			->where(PermissionsViewEntity::ROLE, '!= ?', Conf::ROLE_ADMIN);
	}


	/**
	 * @return array[]|PermissionsViewEntity[]
	 * @throws AttributeDetectionException
	 * @throws Exception
	 */
	public function getAllPermissions(): array
	{
		return $this->getAll()->execute()
			->setRowClass(PermissionsViewEntity::class)
			->fetchAll();
	}
}
