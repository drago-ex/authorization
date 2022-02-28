<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Service\Repository;

use Dibi\Exception;
use Dibi\Row;
use Drago\Attr\Table;
use Drago\Authorization\Conf;
use Drago\Authorization\Service\Entity\RolesEntity;
use Drago\Authorization\NotAllowedChange;
use Drago\Database\Connect;


#[Table(RolesEntity::TABLE, RolesEntity::PRIMARY)]
class RolesRepository extends Connect
{
	/**
	 * @return array[]|RolesEntity[]
	 * @throws Exception
	 */
	public function getAll()
	{
		return $this->all()->fetchAll();
	}


	/**
	 * @throws Exception
	 */
	public function findByParent(int $parent): array|RolesEntity|Row|null
	{
		return $this->discover(RolesEntity::PRIMARY, $parent)->fetch();
	}


	/**
	 * @throws Exception
	 */
	public function getRole(int $id): array|RolesEntity|Row|null
	{
		return $this->get($id)->fetch();
	}


	public function getRoles(): array
	{
		return $this->all()
			->fetchPairs(RolesEntity::PRIMARY, RolesEntity::NAME);
	}


	/**
	 * @throws NotAllowedChange
	 */
	public function findParent(int $id): array|RolesEntity|Row|null
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
