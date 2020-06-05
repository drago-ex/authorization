<?php

declare(strict_types = 1);

/**
 * Drago Extension
 * Package built on Nette Framework
 */

namespace Drago\Authorization\Repository;

use Dibi\Connection;
use Dibi\Fluent;
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
	 * @return array|Entity\PermissionsEntity|null
	 * @throws \Dibi\Exception
	 */
	public function find(int $id)
	{
		return $this->discoverId($id)
			->setRowClass(Entity\PermissionsEntity::class)
			->fetch();
	}


	public function rules(): Fluent
	{
		return $this->all()
			->orderBy('allowed', 'roleId');
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
