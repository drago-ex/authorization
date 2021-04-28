<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization;

use Drago\Authorization\Control\PermissionsBase;
use Drago\Authorization\Control\PrivilegesBase;
use Drago\Authorization\Control\ResourcesBase;
use Drago\Authorization\Control\RolesBase;
use Nette\Application\UI\Presenter;
use Nette\Security\User;


trait Authorization
{
	private RolesBase $rolesControl;
	private ResourcesBase $resourcesControl;
	private PrivilegesBase $privilegesControl;
	private PermissionsBase $permissionsControl;


	public function injectAcl(
		RolesBase $rolesControl,
		ResourcesBase $resourcesControl,
		PrivilegesBase $privilegesControl,
		PermissionsBase $permissionsControl
	) {
		$this->rolesControl = $rolesControl;
		$this->resourcesControl = $resourcesControl;
		$this->privilegesControl = $privilegesControl;
		$this->permissionsControl = $permissionsControl;
	}


	/**
	 * Checks for requirements such as authorization.
	 */
	public function injectPermissions(Presenter $presenter, User $user): void
	{
		$presenter->onStartup[] = function () use ($presenter, $user) {
			$signal = $presenter->getSignal();
			if ((!empty($signal[0])) && isset($signal[1])) {
				if (!$user->isAllowed($presenter->getName(), $signal[0])) {
					$presenter->error('Forbidden', 403);
				}
			} else {
				if (!$user->isAllowed($presenter->getName(), $signal[1] ?? $presenter->getAction())) {
					$presenter->error('Forbidden', 403);
				}
			}
		};
	}


	protected function createComponentRolesControl(): RolesBase
	{
		return $this->rolesControl;
	}


	protected function createComponentResourcesControl(): ResourcesBase
	{
		return $this->resourcesControl;
	}


	protected function createComponentPrivilegesControl(): PrivilegesBase
	{
		return $this->privilegesControl;
	}


	protected function createComponentPermissionsControl(): PermissionsBase
	{
		return $this->permissionsControl;
	}
}
