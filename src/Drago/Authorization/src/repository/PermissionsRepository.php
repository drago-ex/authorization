<?php

declare(strict_types = 1);

/**
 * Drago AclExtension
 * Package built on Nette Framework
 */

namespace Drago\Authorization\Repository;

use Dibi\Connection;
use Dibi\Result;
use Drago\Authorization\Auth;
use Drago\Authorization\Entity\PermissionsEntity;
use Drago\Database\Connect;
use Drago\Database\Repository;
use Nette\Caching\Cache;


class PermissionsRepository extends Connect
{
	use Repository;

	public Cache $cache;
	public string  $table = PermissionsEntity::TABLE;
	public string $columnId = PermissionsEntity::PERMISSION_ID;


	public function __construct(Connection $db, Cache $cache)
	{
		parent::__construct($db);
		$this->cache = $cache;
	}


	private function removeCache(): void
	{
		$this->cache->remove(Auth::ACL_CACHE);
	}


	/**
	 * @throws \Dibi\Exception
	 */
	public function getAll(): Result
	{
		return $this->db
			->query('
				SELECT p.permissionId, p.allowed, r.name resource, p2.name privilege, r2.name role
				FROM permissions p
				    LEFT JOIN resources r ON p.resourceId = r.resourceId
				    LEFT JOIN privileges p2 ON p.privilegeId = p2.privilegeId
				    LEFT JOIN roles r2 ON p.roleId = r2.roleId');
	}


	/**
	 * @throws \Dibi\Exception
	 */
	public function getRoles(): Result
	{
		return $this->db->query('
			SELECT * FROM roles WHERE roleId IN (SELECT DISTINCT roleId FROM permissions)'
		);
	}
}
