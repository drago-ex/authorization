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
use Drago\Attr\AttributeDetectionException;
use Drago\Attr\Table;
use Drago\Authorization\Conf;
use Drago\Authorization\NotAllowedChange;
use Drago\Database\Database;
use Drago\Database\ExtraFluent;


#[Table(PrivilegesEntity::TABLE, PrivilegesEntity::PRIMARY)]
class PrivilegesRepository
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
			->where(PrivilegesEntity::NAME, '!= ?', Conf::PRIVILEGE_ALL)
			->orderBy(PrivilegesEntity::NAME, 'asc');
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
		if ($privilege === Conf::PRIVILEGE_ALL) {
			throw new NotAllowedChange(
				'The record is not allowed to be edited or deleted.',
				1001,
			);
		}
		return true;
	}


	/**
	 * @throws AttributeDetectionException
	 */
	public function remove(int $id): ExtraFluent
	{
		return $this->delete(PrivilegesEntity::PRIMARY, $id);
	}
}
