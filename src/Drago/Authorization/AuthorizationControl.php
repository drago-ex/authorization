<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control;


trait AuthorizationControl
{
	private RolesControl $rolesControl;
	private ResourcesControl $resourcesControl;
	private PrivilegesControl $privilegesControl;
	private PermissionsControl $permissionsControl;
	private ResetControl $resetControl;


	public function injectAuthorizationControl(
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


	protected function createComponentResetControl(): ResetControl
	{
		return $this->resetControl;
	}
}
