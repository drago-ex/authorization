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


	public function injectAuthorizationComponents(Control\RolesControl $rolesControl, Control\ResourcesControl $resourcesControl)
	{
		$this->roles = $rolesControl;
		$this->resources = $resourcesControl;
	}


	/**
	 * @return Control\RolesControl
	 */
	protected function createComponentRolesControl()
	{
		return $this->roles;
	}


	/**
	 * @return Control\ResourcesControl
	 */
	protected function createComponentResourcesControl()
	{
		return $this->resources;
	}
}
