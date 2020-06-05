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
	private $rolesControl;

	/** @var Control\ResourcesControl */
	private $resourcesControl;

	/** @var Control\PrivilegesControl */
	private $privilegesControl;

	/** @var Control\PermissionsControl */
	private $permissionsControl;


	public function injectAuthorization(
		Control\RolesControl $rolesControl,
		Control\ResourcesControl $resourcesControl,
		Control\PrivilegesControl $privilegesControl,
		Control\PermissionsControl $permissionsControl)
	{
		$this->rolesControl = $rolesControl;
		$this->resourcesControl = $resourcesControl;
		$this->privilegesControl = $privilegesControl;
		$this->permissionsControl = $permissionsControl;
	}


	protected function createComponentRolesControl(): Control\RolesControl
	{
		return $this->rolesControl;
	}


	protected function createComponentResourcesControl(): Control\ResourcesControl
	{
		return $this->resourcesControl;
	}


	protected function createComponentPrivilegesControl(): Control\PrivilegesControl
	{
		return $this->privilegesControl;
	}


	protected function createComponentPermissionsControl(): Control\PermissionsControl
	{
		return $this->permissionsControl;
	}
}
