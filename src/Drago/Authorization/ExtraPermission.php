<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization;

use Dibi\DriverException;
use Dibi\Exception;
use Drago\Authorization\Service\Repository\PermissionsViewRepository;
use Drago\Authorization\Service\Repository\ResourcesRepository;
use Drago\Authorization\Service\Repository\RolesRepository;
use Nette\Caching\Cache;
use Nette\Security\Authorizator;
use Nette\Security\Permission;
use Throwable;


/**
 * Managing user permissions.
 */
class ExtraPermission
{
	public function __construct(
		private Cache $cache,
		private RolesRepository $roles,
		private ResourcesRepository $resources,
		private PermissionsViewRepository $permissions,
	) {
	}


	/**
	 * @throws Exception
	 * @throws Throwable
	 */
	public function create(): Permission
	{
		$acl = new Permission;
		try {
			if (!$this->cache->load(Conf::CACHE)) {
				foreach ($this->roles->getAll() as $role) {
					$parent = $this->roles->findByParent($role->parent);
					$acl->addRole($role->name, $parent->name ?? null);
				}

				foreach ($this->resources->getAll() as $resource) {
					$acl->addResource($resource->name);
				}

				foreach ($this->permissions->getAll() as $row) {
					$row->privilege = $row->privilege === Conf::PRIVILEGE_ALL
						? Authorizator::ALL
						: $row->privilege;
					$acl->{$row->allowed === 'yes'
						? 'allow'
						: 'deny'} ($row->role, $row->resource, $row->privilege);
				}

				$acl->allow(Conf::ROLE_ADMIN, Authorizator::ALL, Authorizator::ALL);
				$this->cache->save(Conf::CACHE, $acl);
			}

			if ($this->cache->load(Conf::CACHE)) {
				$acl = $this->cache->load(Conf::CACHE);
			}
		} catch (DriverException $e) {
			// Not implemented.
		}
		return $acl;
	}
}
