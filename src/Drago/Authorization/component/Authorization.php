<?php

declare(strict_types = 1);

/**
 * Drago Extension
 * Package built on Nette Framework
 */

namespace Drago\Authorization;

use Drago\Authorization\Control;


/**
 * Dynamic processing of user roles.
 */
trait Authorization
{
	/** @var Control\RolesControl */
	private $roles;

	/** @var Control\ResourcesControl */
	private $resources;

	/** @var Control\PrivilegesControl */
	private $privileges;


	public function injectAuthorizationComponents(
		Control\RolesControl $rolesControl,
		Control\ResourcesControl $resourcesControl,
		Control\PrivilegesControl $privilegesControl)
	{
		$this->roles = $rolesControl;
		$this->resources = $resourcesControl;
		$this->privileges = $privilegesControl;
	}


	protected function createComponentRolesControl(): Control\RolesControl
	{
		return $this->roles;
	}


	protected function createComponentResourcesControl(): Control\ResourcesControl
	{
		return $this->resources;
	}


	protected function createComponentPrivilegesControl(): Control\PrivilegesControl
	{
		return $this->privileges;
	}
}
