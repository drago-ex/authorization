<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control;


trait Authorization
{
	private RolesControl $rolesControl;
	private ResourcesControl $resourcesControl;
	private PrivilegesControl $privilegesControl;
	private PermissionsControl $permissionsControl;
	private ResetControl $resetControl;


	public function injectAcl(
		RolesControl $rolesControl,
		ResourcesControl $resourcesControl,
		PrivilegesControl $privilegesControl,
		PermissionsControl $permissionsControl,
		ResetControl $resetControl,
	) {
		$this->rolesControl = $rolesControl;
		$this->resourcesControl = $resourcesControl;
		$this->privilegesControl = $privilegesControl;
		$this->permissionsControl = $permissionsControl;
		$this->resetControl = $resetControl;
	}


	protected function createComponentRolesControl(): RolesControl
	{
		return $this->rolesControl;
	}


	protected function createComponentResourcesControl(): ResourcesControl
	{
		return $this->resourcesControl;
	}


	protected function createComponentPrivilegesControl(): PrivilegesControl
	{
		return $this->privilegesControl;
	}


	protected function createComponentPermissionsControl(): PermissionsControl
	{
		return $this->permissionsControl;
	}


	protected function createComponentResetControl(): ResetControl
	{
		return $this->resetControl;
	}
}
