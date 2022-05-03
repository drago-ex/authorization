<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Access;

use Dibi\Connection;
use Drago\Attr\AttributeDetectionException;
use Drago\Attr\Table;
use Drago\Database\Repository;
use Nette\SmartObject;


#[Table(AccessEntity::TABLE, AccessEntity::PRIMARY)]
class UsersRepository
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
	public function getAllUsers(): array
	{
		return $this->all()
			->fetchPairs(AccessEntity::PRIMARY, AccessEntity::USERNAME);
	}
}
