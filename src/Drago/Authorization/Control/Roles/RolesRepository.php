<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

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


/**
 * Repository for interacting with the roles data in the database.
 * Provides methods to fetch, update, and manage roles.
 *
 * @extends Database<RolesEntity>
 */
#[Table(RolesEntity::Table, RolesEntity::PrimaryKey, class: RolesEntity::class)]
class RolesRepository
{
	use Database;

	public function __construct(
		protected Connection $connection,
	) {
	}


	/**
	 * Fetch all roles from the database, ordered by the primary key.
	 *
	 * @throws AttributeDetectionException
	 */
	public function getAll(): ExtraFluent
	{
		return $this->read('*')
			->orderBy(RolesEntity::PrimaryKey);
	}


	/**
	 * Find a role by its parent ID.
	 *
	 * @param int $parent Parent ID to search for.
	 * @return array|RolesEntity|null The role(s) or null if not found.
	 *
	 * @throws Exception
	 * @throws AttributeDetectionException
	 */
	public function findByParent(int $parent): array|RolesEntity|null
	{
		return $this->find(RolesEntity::PrimaryKey, $parent)
			->record();
	}


	/**
	 * Fetch all roles excluding the admin role.
	 *
	 * @return array List of roles.
	 *
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
	 *
	 * @return array Key-value pairs of roles.
	 *
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
	 * Throws an exception if there are records associated with this role.
	 *
	 * @param int $id The role ID to search for.
	 * @return array|RolesEntity|null The parent role or null if not found.
	 *
	 * @throws NotAllowedChange If the role cannot be deleted due to associations.
	 * @throws AttributeDetectionException
	 */
	public function findParent(int $id): array|RolesEntity|null
	{
		$row = $this->find(RolesEntity::ColumnParent, $id)->fetch();
		if ($row) {
			throw new NotAllowedChange(
				'The record cannot be deleted, you must first delete the records that are associated with it.',
				1002,
			);
		}
		return $row;
	}


	/**
	 * Check if a role is allowed to be updated or deleted.
	 * Throws an exception if not allowed.
	 *
	 * @param string $role The role to check.
	 * @return bool True if the role can be edited or deleted, false otherwise.
	 *
	 * @throws NotAllowedChange If the role is not allowed to be edited or deleted.
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
