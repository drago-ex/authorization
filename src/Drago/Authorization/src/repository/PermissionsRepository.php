<?php

declare(strict_types = 1);

/**
 * Drago Extension
 * Package built on Nette Framework
 */

namespace Drago\Authorization\Repository;

use Dibi\Connection;
use Dibi\Result;
use Drago\Authorization\Authorizator;
use Drago\Authorization\Entity;
use Drago\Database;
use Nette\Caching;


class PermissionsRepository extends Database\Connect
{
	use Database\Repository;

	/** @var Caching\Cache */
	public $cache;

	/** @var string */
	private $table = Entity\PermissionsEntity::TABLE;

	/** @var string */
	private $primaryId = Entity\PermissionsEntity::PERMISSION_ID;


	public function __construct(Connection $db, Caching\Cache $cache)
	{
		parent::__construct($db);
		$this->cache = $cache;
	}


	private function removeCache(): void
	{
		$this->cache->remove(Authorizator::ACL_CACHE);
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
	 * @return array|Entity\PermissionsEntity|null
	 * @throws \Dibi\Exception
	 */
	public function find(int $id)
	{
		return $this->discoverId($id)
			->setRowClass(Entity\PermissionsEntity::class)
			->fetch();
	}


	/**
	 * @throws \Dibi\Exception
	 */
	public function findRoles(): Result
	{
		return $this->db->query('
			SELECT * FROM roles WHERE roleId IN (SELECT DISTINCT roleId FROM permissions)'
		);
	}


	/**
	 * @throws \Dibi\Exception
	 */
	public function rules(): Result
	{
		return $this->db
			->query('SELECT * FROM permissions GROUP BY allowed, roleId');
	}


	/**
	 * @throws \Dibi\Exception
	 */
	public function delete(int $id): void
	{
		$this->eraseId($id);
		$this->removeCache();
	}


	/**
	 * @throws \Dibi\Exception
	 */
	public function save(Entity\PermissionsEntity $entity): void
	{
		$id = $entity->getPermissionId();
		$this->put($entity, $id);
		$this->removeCache();
	}
}
