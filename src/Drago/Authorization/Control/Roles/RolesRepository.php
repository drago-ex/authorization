<?php

declare(strict_types=1);

namespace Drago\Authorization\Control\Roles;

use Dibi\Connection;
use Dibi\Exception;
use Drago\Attr\AttributeDetectionException;
use Drago\Attr\Table;
use Drago\Authorization\Conf;
use Drago\Authorization\NotAllowedChange;
use Drago\Database\Database;
use Drago\Database\ExtraFluent;


/** Repository for interacting with the roles data in the database. */
#[Table(RolesEntity::Table, RolesEntity::PrimaryKey, class: RolesEntity::class)]
class RolesRepository
{
	/** @phpstan-use Database<RolesEntity> */
	use Database;

	public function __construct(
		protected Connection $connection,
	) {
	}


	/**
	 * Fetch all roles from the database, ordered by the primary key.
	 * @return ExtraFluent<RolesEntity>
	 * @throws AttributeDetectionException
	 */
	public function getAll(): ExtraFluent
	{
		return $this->read('*')
			->orderBy(RolesEntity::PrimaryKey);
	}


	/**
	 * Find a role by its parent ID.
	 * @return array<string, mixed>|RolesEntity|null
	 * @throws AttributeDetectionException
	 * @throws Exception
	 */
	public function findByParent(int $parent): array|RolesEntity|null
	{
		return $this->find(RolesEntity::PrimaryKey, $parent)
			->record();
	}


	/**
	 * Fetch all roles excluding the admin role.
	 * @return array<int, string>
	 * @throws AttributeDetectionException
	 */
	public function getRoles(): array
	{
		return $this->read('*')
			->where(RolesEntity::ColumnName, '!= ?', Conf::RoleAdmin)
			->fetchPairs(RolesEntity::PrimaryKey, RolesEntity::ColumnName);
	}


	/**
	 * Fetch all roles in a key-value pair format, excluding the admin role.
	 * @return array<string, string>
	 * @throws AttributeDetectionException
	 */
	public function getRolesPairs(): array
	{
		return $this->read('*')
			->where(RolesEntity::ColumnName, '!= ?', Conf::RoleAdmin)
			->fetchPairs(RolesEntity::ColumnName, RolesEntity::ColumnName);
	}


	/**
	 * Find the parent of a role by its ID.
	 * @return array<string, mixed>|RolesEntity|null
	 * @throws AttributeDetectionException
	 * @throws NotAllowedChange
	 */
	public function findParent(int $id): array|RolesEntity|null
	{
		/** @var array<string, mixed>|RolesEntity|null $row */
		$row = $this->find(RolesEntity::ColumnParent, $id)->fetch();
		if ($row) {
			throw new NotAllowedChange(
				'The record cannot be deleted, you must first delete the records that are associated with it.',
				1002,
			);
		}
		return $row;
	}


	/** Check if a role is allowed to be updated or deleted.
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
