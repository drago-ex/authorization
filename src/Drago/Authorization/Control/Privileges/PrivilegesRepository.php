<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Privileges;

use Dibi\Connection;
use Dibi\Exception;
use Dibi\Fluent;
use Dibi\Result;
use Drago\Attr\AttributeDetectionException;
use Drago\Attr\Table;
use Drago\Authorization\Conf;
use Drago\Authorization\NotAllowedChange;
use Drago\Database\Repository;
use Nette\SmartObject;


#[Table(PrivilegesEntity::Table, PrivilegesEntity::Id)]
class PrivilegesRepository
{
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
			->where(PrivilegesEntity::Name, '!= ?', Conf::PrivilegeAll)
			->orderBy(PrivilegesEntity::Name, 'asc');
	}


	/**
	 * @throws Exception
	 * @throws AttributeDetectionException
	 */
	public function getOne(int $id): array|PrivilegesEntity|null
	{
		return $this->get($id)->execute()
			->setRowClass(PrivilegesEntity::class)
			->fetch();
	}


	/**
	 * @throws NotAllowedChange
	 */
	public function isAllowed(string $privilege): bool
	{
		if ($privilege === Conf::PrivilegeAll) {
			throw new NotAllowedChange(
				'The record is not allowed to be edited or deleted.',
				1001,
			);
		}
		return true;
	}


	/**
	 * @throws Exception
	 * @throws AttributeDetectionException
	 */
	public function save(PrivilegesData $data): Result|int|null
	{
		return $this->put($data->toArray());
	}
}
