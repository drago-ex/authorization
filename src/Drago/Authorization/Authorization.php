<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization;

use Drago\Authorization\Control\PermissionsControl;
use Drago\Authorization\Control\PrivilegesControl;
use Drago\Authorization\Control\ResourcesControl;
use Drago\Authorization\Control\RolesControl;
use Nette\Application\UI\Presenter;
use Nette\Security\User;


trait Authorization
{
	private RolesControl $rolesControl;
	private ResourcesControl $resourcesControl;
	private PrivilegesControl $privilegesControl;
	private PermissionsControl $permissionsControl;


	public function injectAcl(
		RolesControl $rolesControl,
		ResourcesControl $resourcesControl,
		PrivilegesControl $privilegesControl,
		PermissionsControl $permissionsControl
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
					$this->error('Forbidden', 403);
				}
			} else {
				if (!$user->isAllowed($presenter->getName(), $signal[1] ?? $presenter->getAction())) {
					$this->error('Forbidden', 403);
				}
			}
		};
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
}