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
	protected $roles;


	public function injectAuthorizationComponent(Control\RolesControl $roles)
	{
		$this->roles = $roles;
	}


	/**
	 * @return Control\RolesControl
	 */
	protected function createComponentAclRoles()
	{
		$roles = $this->roles;
		return $roles;
	}
}
