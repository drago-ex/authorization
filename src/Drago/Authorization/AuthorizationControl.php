<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control;


trait AuthorizationControl
{
	public RolesControl $rolesControl;
	public ResourcesControl $resourcesControl;
	public PrivilegesControl $privilegesControl;
	public PermissionsControl $permissionsControl;
	public AccessControl $accessControl;
	private ResetControl $resetControl;


	public function injectAuthorizationControl(
		RolesControl $rolesControl,
		ResourcesControl $resourcesControl,
		PrivilegesControl $privilegesControl,
		PermissionsControl $permissionsControl,
		AccessControl $accessControl,
		ResetControl $resetControl,
	) {
		$this->rolesControl = $rolesControl;
		$this->resourcesControl = $resourcesControl;
		$this->privilegesControl = $privilegesControl;
		$this->permissionsControl = $permissionsControl;
		$this->accessControl = $accessControl;
		$this->resetControl = $resetControl;
	}


	protected function createComponentResetControl(): ResetControl
	{
		return $this->resetControl;
	}
}
