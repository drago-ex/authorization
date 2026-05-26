<?php

declare(strict_types=1);

namespace Drago\Authorization\Control\Roles;

use Dibi\Connection;
use Drago\Attr\Table;
use Drago\Authorization\Conf;
use Drago\Authorization\NotAllowedChange;
use Drago\Database\Database;
use Drago\Database\ExtraFluent;


/** Repository for interacting with the roles data in the database. */
#[Table(RolesEntity::Table, RolesEntity::PrimaryKey, class: RolesEntity::class)]
class RolesRepository
{
	/** @use Database<RolesEntity> */
	use Database;

	public function __construct(
		protected Connection $connection,
	) {
	}


	/**
	 * Fetch all roles from the database, ordered by the primary key.
	 * @return ExtraFluent<RolesEntity>
	 */
	public function getAll(): ExtraFluent
	{
		return $this->read('*')
			->orderBy(RolesEntity::PrimaryKey);
	}


	/**
	 * Find a role by its parent ID.
	 * @return array<string, mixed>|RolesEntity|null
	 */
	public function findByParent(int $parent): array|RolesEntity|null
	{
		/** @var array<string, mixed>|RolesEntity|null $record */
		$record = $this->find(RolesEntity::PrimaryKey, $parent)
			->record();
		return $record;
	}


	/**
	 * Fetch all roles excluding the admin role.
	 * @return array<int, string>
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


	/** Check if a role is allowed to be updated or deleted. */
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
