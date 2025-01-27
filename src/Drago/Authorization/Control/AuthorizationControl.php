<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control;

use Drago\Authorization\Control\Access\AccessControl;
use Drago\Authorization\Control\Permissions\PermissionsControl;
use Drago\Authorization\Control\Privileges\PrivilegesControl;
use Drago\Authorization\Control\Resources\ResourcesControl;
use Drago\Authorization\Control\Roles\RolesControl;


/**
 * Trait that provides the injection and management of various authorization controls.
 */
trait AuthorizationControl
{
	public RolesControl $rolesControl;
	public ResourcesControl $resourcesControl;
	public PrivilegesControl $privilegesControl;
	public PermissionsControl $permissionsControl;
	public AccessControl $accessControl;


	public function injectAuthorizationControl(
		RolesControl $rolesControl,
		ResourcesControl $resourcesControl,
		PrivilegesControl $privilegesControl,
		PermissionsControl $permissionsControl,
		AccessControl $accessControl,
	): void
	{
		$this->rolesControl = $rolesControl;
		$this->resourcesControl = $resourcesControl;
		$this->privilegesControl = $privilegesControl;
		$this->permissionsControl = $permissionsControl;
		$this->accessControl = $accessControl;
	}
}
