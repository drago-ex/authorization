<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Roles;

use Dibi\Connection;
use Dibi\Exception;
use Dibi\Fluent;
use Dibi\Result;
use Dibi\Row;
use Drago\Attr\AttributeDetectionException;
use Drago\Attr\Table;
use Drago\Authorization\Conf;
use Drago\Authorization\NotAllowedChange;
use Drago\Database\Database;


#[Table(RolesEntity::TABLE, RolesEntity::PRIMARY)]
class RolesRepository
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
			->orderBy(RolesEntity::PRIMARY);
	}


	/**
	 * @return array[]|RolesEntity[]
	 * @throws AttributeDetectionException
	 * @throws Exception
	 */
	public function getAllRoles(): array
	{
		return $this->getAll()->execute()
			->setRowClass(RolesEntity::class)
			->fetchAll();
	}


	/**
	 * @throws Exception
	 * @throws AttributeDetectionException
	 */
	public function findByParent(int $parent): array|RolesEntity|null
	{
		return $this->find(RolesEntity::PRIMARY, $parent)
			->execute()->setRowClass(RolesEntity::class)
			->fetch();
	}


	/**
	 * @throws Exception
	 * @throws AttributeDetectionException
	 */
	public function getOne(int $id): array|RolesEntity|null
	{
		return $this->get($id)->execute()
			->setRowClass(RolesEntity::class)
			->fetch();
	}


	/**
	 * @throws AttributeDetectionException
	 */
	public function getRoles(): array
	{
		return $this->read('*')
			->where(RolesEntity::NAME, '!= ?', Conf::ROLE_GUEST)
			->fetchPairs(RolesEntity::PRIMARY, RolesEntity::NAME);
	}


	/**
	 * @return array[]|RolesEntity[]
	 * @throws AttributeDetectionException
	 * @throws Exception
	 */
	public function getRolesAll(): array
	{
		return $this->read('*')
			->where(RolesEntity::NAME, '!= ?', Conf::ROLE_GUEST)
			->execute()->setRowClass(RolesEntity::class)->fetchAll();
	}


	/**
	 * @return array[]|Row[]
	 * @throws AttributeDetectionException
	 * @throws Exception
	 */
	public function getAllRolesForPermissions(): array
	{
		return $this->read('*')
			->where(RolesEntity::NAME, '!= ?', Conf::ROLE_ADMIN)
			->execute()->setRowClass(RolesEntity::class)->fetchAll();
	}


	/**
	 * @throws AttributeDetectionException
	 */
	public function getRolesPairs(): array
	{
		return $this->read('*')
			->where(RolesEntity::NAME, ' != ?', Conf::ROLE_ADMIN)
			->fetchPairs(RolesEntity::NAME, RolesEntity::NAME);
	}


	/**
	 * @throws NotAllowedChange
	 * @throws AttributeDetectionException
	 */
	public function findParent(int $id): array|RolesEntity|Row|null
	{
		$row = $this->find(RolesEntity::PARENT, $id)->fetch();
		if ($row) {
			throw new NotAllowedChange(
				'The record can not be deleted, you must first delete the records that are associated with it.',
				1002,
			);
		}
		return $row;
	}


	/**
	 * @throws NotAllowedChange
	 */
	public function isAllowed(string $role): bool
	{
		if (isset(Conf::$roles[$role])) {
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
	public function remove(int $id): Result|int|null
	{
		return $this->delete(RolesEntity::PRIMARY, $id)
			->execute();
	}
}
