<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization;

use Dibi\DriverException;
use Dibi\Exception;
use Drago\Authorization\Control\Permissions\PermissionsViewRepository;
use Drago\Authorization\Control\Resources\ResourcesRepository;
use Drago\Authorization\Control\Roles\RolesRepository;
use Nette\Caching\Cache;
use Nette\Security\Authorizator;
use Nette\Security\Permission;
use Nette\SmartObject;
use Throwable;


/**
 * Managing user permissions.
 */
class ExtraPermission
{
	use SmartObject;

	public function __construct(
		private readonly Cache $cache,
		private readonly RolesRepository $rolesRepository,
		private readonly ResourcesRepository $resourcesRepository,
		private readonly PermissionsViewRepository $permissionsViewRepository,
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
			if (!$this->cache->load(Conf::Cache)) {
				$rolesQueue = $this->rolesRepository->getAllRoles();
				while (count($rolesQueue) > 0) {
					$role = array_shift($rolesQueue);
					$parent = $this->rolesRepository->findByParent($role->parent);
					$parent === null || in_array($parent->name, $acl->getRoles(), true)
						? $acl->addRole($role->name, $parent->name ?? null)
						: array_push($rolesQueue, $role);
				}

				foreach ($this->resourcesRepository->getAllResources() as $resource) {
					$acl->addResource($resource->name);
				}

				foreach ($this->permissionsViewRepository->getAllPermissions() as $row) {
					$row->privilege = $row->privilege === Conf::PrivilegeAll
						? Authorizator::ALL
						: $row->privilege;
					$acl->{$row->allowed === 1
						? 'allow'
						: 'deny'} ($row->role, $row->resource, $row->privilege);
				}

				$acl->allow(Conf::RoleAdmin);
				$this->cache->save(Conf::Cache, $acl);
			}

			if ($this->cache->load(Conf::Cache)) {
				$acl = $this->cache->load(Conf::Cache);
			}
		} catch (DriverException) {
			// Not implemented.
		}

		return $acl;
	}
}
