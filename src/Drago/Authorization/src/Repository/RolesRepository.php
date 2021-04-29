<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Repository;

use Drago\Authorization\Conf;
use Drago\Authorization\Entity\RolesEntity;
use Drago\Authorization\NotAllowedChange;
use Drago\Database\Connect;
use Drago\Database\Repository;


class RolesRepository extends Connect
{
	use Repository;

	public string $table = RolesEntity::TABLE;
	public string $primary = RolesEntity::PRIMARY;


	/**
	 * @return array[]|RolesEntity[]
	 * @throws \Dibi\Exception
	 */
	public function getAll()
	{
		return $this->all()->execute()
			->setRowClass(RolesEntity::class)
			->fetchAll();
	}


	/**
	 * @throws \Dibi\Exception
	 */
	public function findByParent(int $parent): array|RolesEntity|null
	{
		return $this->discover(RolesEntity::PRIMARY, $parent)->execute()
			->setRowClass(RolesEntity::class)
			->fetch();
	}


	/**
	 * @throws \Dibi\Exception
	 */
	public function getRole(int $id): array|RolesEntity|null
	{
		return $this->get($id)->execute()
			->setRowClass(RolesEntity::class)
			->fetch();
	}


	public function getRoles(): array
	{
		return $this->all()
			->fetchPairs(RolesEntity::PRIMARY, RolesEntity::NAME);
	}


	/**
	 * @throws NotAllowedChange
	 */
	public function findParent(int $id): array|RolesEntity|null
	{
		$row = $this->discover(RolesEntity::PARENT, $id)->fetch();
		if ($row) {
			throw new NotAllowedChange('The record can not be deleted, you must first delete the records that are associated with it.', 1002);
		}
		return $row;
	}


	/**
	 * @throws NotAllowedChange
	 */
	public function isAllowed(string $role): bool
	{
		if (isset(Conf::$roles[$role])) {
			throw new NotAllowedChange('The record is not allowed to be edited or deleted.', 1001);
		}
		return true;
	}
}
