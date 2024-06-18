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


#[Table(UsersDepartmentsEntity::TABLE, UsersDepartmentsEntity::PRIMARY)]
class UsersDepartmentsRepository
{
	use Repository;

	public function __construct(
		protected Connection $db,
	) {
	}


	/**
	 * @return Result|int|null
	 * @throws AttributeDetectionException
	 * @throws Exception
	 */
	public function deleteByUserId(int $userId)
	{
		return $this->db->delete($this->getTable())->where(UsersDepartmentsEntity::USER_ID, '= ?', $userId)
			->execute();
	}


	/**
	 * @return UsersDepartmentsEntity[]
	 * @throws AttributeDetectionException
	 * @throws Exception
	 */
	public function findByUserId(int $userId): array
	{
		return $this->discover(UsersDepartmentsEntity::USER_ID, $userId)
			->execute()->setRowClass(UsersDepartmentsEntity::class)
			->fetchAll();
	}
}
