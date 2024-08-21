<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Roles;

use Dibi\Exception;
use Drago\Attr\AttributeDetectionException;
use Drago\Attr\From;
use Drago\Authorization\Conf;
use Drago\Authorization\NotAllowedChange;
use Drago\Database\Database;
use Drago\Database\FluentExtra;


#[From(RolesEntity::Table, RolesEntity::PrimaryKey, class: RolesEntity::class)]
class RolesRepository extends Database
{
	/**
	 * @throws AttributeDetectionException
	 */
	public function getAll(): FluentExtra
	{
		return $this->read()
			->orderBy(RolesEntity::PrimaryKey);
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
		return $this->find(RolesEntity::PrimaryKey, $parent)
			->record();
	}


	/**
	 * @throws Exception
	 * @throws AttributeDetectionException
	 */
	public function getOne(int $id): array|RolesEntity|null
	{
		return $this->find(RolesEntity::PrimaryKey, $id)
			->record();
	}


	/**
	 * @throws AttributeDetectionException
	 */
	public function getRoles(): array
	{
		return $this->read()
			->where(RolesEntity::ColumnName, '!= ?', Conf::RoleAdmin)
			->fetchPairs(RolesEntity::PrimaryKey, RolesEntity::ColumnName);
	}


	/**
	 * @throws AttributeDetectionException
	 */
	public function getRolesPairs(): array
	{
		return $this->read()
			->where(RolesEntity::ColumnName, '!= ?', Conf::RoleAdmin)
			->fetchPairs(RolesEntity::ColumnName, RolesEntity::ColumnName);
	}


	/**
	 * @throws NotAllowedChange
	 * @throws AttributeDetectionException
	 */
	public function findParent(int $id): array|RolesEntity|null
	{
		$row = $this->find(RolesEntity::ColumnParent, $id)->fetch();
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
}
