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
use Drago\Database\Database;


#[Table(UsersDepartmentsEntity::TABLE, UsersDepartmentsEntity::PRIMARY)]
class UsersDepartmentsRepository
{
	use Database;

	public function __construct(
		protected Connection $connection,
	) {
	}


	/**
	 * @throws AttributeDetectionException
	 * @throws Exception
	 */
	public function deleteByUserId(int $userId): Result|int|null
	{
		return $this->delete(UsersDepartmentsEntity::USER_ID, $userId)
			->execute();
	}


	/**
	 * @return UsersDepartmentsEntity[]
	 * @throws AttributeDetectionException
	 * @throws Exception
	 */
	public function findByUserId(int $userId): array
	{
		return $this->find(UsersDepartmentsEntity::USER_ID, $userId)
			->execute()->setRowClass(UsersDepartmentsEntity::class)
			->fetchAll();
	}
}
