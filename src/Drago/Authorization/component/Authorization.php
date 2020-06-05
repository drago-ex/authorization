<?php

declare(strict_types = 1);

/**
 * Drago Extension
 * Package built on Nette Framework
 */

namespace Drago\Authorization;

use Drago\Authorization\Control;


trait Authorization
{
	/** @var Control\RolesControl */
	private $roles;

	/** @var Control\ResourcesControl */
	private $resources;

	/** @var Control\PrivilegesControl */
	private $privileges;

	/** @var Control\PermissionsControl */
	private $permissions;


	public function injectAuthorization(
		Control\RolesControl $roles,
		Control\ResourcesControl $resources,
		Control\PrivilegesControl $privileges,
		Control\PermissionsControl $permissions)
	{
		$this->roles = $roles;
		$this->resources = $resources;
		$this->privileges = $privileges;
		$this->permissions = $permissions;
	}


	protected function createComponentRoles(): Control\RolesControl
	{
		return $this->roles;
	}


	protected function createComponentResources(): Control\ResourcesControl
	{
		return $this->resources;
	}


	protected function createComponentPrivileges(): Control\PrivilegesControl
	{
		return $this->privileges;
	}


	protected function createComponentPermissions(): Control\PermissionsControl
	{
		return $this->permissions;
	}
}
