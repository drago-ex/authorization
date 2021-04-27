<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization;

use Dibi\Exception;
use Drago\Authorization\Repository\PermissionsViewRepository;
use Drago\Authorization\Repository\ResourcesRepository;
use Drago\Authorization\Repository\RolesRepository;
use Nette\Caching\Cache;
use Nette\Security\Permission;
use Throwable;


/**
 * Managing user permissions.
 */
class Acl
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
		if (!$this->cache->load(Conf::CACHE)) {
			foreach ($this->roles->getAll() as $role) {
				$parent = $this->roles->findByParent($role->parent);
				$acl->addRole($role->name, $parent->name ?? null);
			}

			foreach ($this->resources->getAll() as $resource) {
				$acl->addResource($resource->name);
			}

			foreach ($this->permissions->getAll() as $row) {
				$row->privilege === Conf::PRIVILEGE_ALL ? $row->privilege = Permission::ALL : $row->privilege;
				$acl->{$row->allowed === 'yes' ? 'allow' : 'deny'} ($row->role, $row->resource, $row->privilege);
			}

			$acl->addRole(Conf::ROLE_ADMIN, Conf::ROLE_MEMBER);
			$acl->allow(Conf::ROLE_ADMIN, Permission::ALL, Permission::ALL);
			$this->cache->save(Conf::CACHE, $acl);
		}

		if ($this->cache->load(Conf::CACHE)) {
			$acl = $this->cache->load(Conf::CACHE);
		}
		return $acl;
	}
}
