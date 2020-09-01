<?php

declare(strict_types = 1);

/**
 * Drago AclExtension
 * Package built on Nette Framework
 */

namespace Drago\Authorization;

use Dibi\Exception;
use Drago\Authorization\Entity\RolesEntity;
use Drago\Authorization\Repository\PermissionsRepository;
use Drago\Authorization\Repository\ResourcesRepository;
use Drago\Authorization\Repository\RolesRepository;
use Nette\Caching\Cache;
use Nette\Security\IAuthorizator;
use Nette\Security\Permission;
use Tracy\Debugger;


/**
 * Managing User Permissions.
 */
class Authorizator
{
	private RolesRepository $roles;
	private ResourcesRepository $resources;
	private PermissionsRepository $permissions;
	private Cache $cache;


	public function __construct(
		Cache $cache,
		RolesRepository $roles,
		ResourcesRepository $resources,
		PermissionsRepository $permissions
	) {
		$this->cache = $cache;
		$this->roles = $roles;
		$this->permissions = $permissions;
		$this->resources = $resources;
	}


	/**
	 * @return IAuthorizator
	 * @throws Exception
	 */
	public function create(): Permission
	{
		$acl = new Permission;
		if (!$this->cache->load(Auth::ACL_CACHE)) {

			// Add roles.
			foreach ($this->roles->all() as $role) {

				/** @var RolesEntity $find */
				$find = $this->roles->discover(RolesEntity::ROLE_ID, $role->parent)->fetch();
				$role->parentName = $find->name ?? null;
				$acl->addRole($role->name, $role->parentName);
			}

			// Add resources.
			foreach ($this->resources->all() as $resource) {
				$acl->addResource($resource->name);
			}

			// Add permissions.
			foreach ($this->permissions->getAll() as $row) {
				$row->privilege === Auth::PRIVILEGE_ALL ? $row->privilege = Permission::ALL : $row->privilege;
				$acl->{$row->allowed === 'yes' ? 'allow' : 'deny'}($row->role, $row->resource, $row->privilege);
			}

			// Admin role that can do everything.
			$acl->allow(Auth::ROLE_ADMIN, Permission::ALL, Permission::ALL);

			// Save permissions to cache.
			$this->cache->save(Auth::ACL_CACHE, $acl);
		}

		// Load permissions form cache.
		$aclCache = [];
		if ($this->cache->load(Auth::ACL_CACHE)) {
			$aclCache = $this->cache->load(Auth::ACL_CACHE);
		}
		return $aclCache;
	}
}
