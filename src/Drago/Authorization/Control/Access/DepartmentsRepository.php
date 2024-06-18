<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Access;

use Dibi\Connection;
use Dibi\Exception;
use Dibi\Result;
use Drago\Attr\AttributeDetectionException;
use Drago\Attr\Table;
use Drago\Database\Repository;


#[Table(DepartmentsEntity::TABLE)]
class DepartmentsRepository
{
	use Repository;

	public function __construct(
		protected Connection $db,
	) {
	}


	/**
	 * @throws AttributeDetectionException
	 * @throws Exception
	 */
	public function getAll(): UsersDepartmentsEntity
	{
		return $this->all()->execute()
			->setRowClass(UsersDepartmentsEntity::class)
			->fetchPairs('id', 'name');
	}
}
