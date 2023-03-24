<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Permissions;

use Dibi\Connection;
use Dibi\Fluent;
use Drago\Attr\AttributeDetectionException;
use Drago\Attr\Table;
use Drago\Authorization\Conf;
use Drago\Database\Repository;
use Nette\SmartObject;


#[Table(PermissionsViewEntity::TABLE)]
class PermissionsViewRepository
{
	use SmartObject;
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
			->where(PermissionsViewEntity::ROLE, '!= ?', Conf::ROLE_ADMIN);
	}
}
