<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control;

use Drago\Authorization\FileNotFoundException;


/**
 * @property string $rolesTemplateAdd
 * @property string $rolesTemplateRecords
 *
 * @property string $resourcesTemplateAdd
 * @property string $resourcesTemplateRecords
 *
 * @property string $privilegesTemplateAdd
 * @property string $privilegesTemplateRecords
 *
 * @property string $permissionsTemplateAdd
 * @property string $permissionsTemplateRecords
 */
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


	/**
	 * @throws FileNotFoundException
	 */
	protected function createComponentRolesControl(): RolesControl
	{
		$control = $this->rolesControl;
		if (isset($this->rolesTemplateAdd)) {
			$control->setTemplateFile($this->rolesTemplateAdd);
		}
		if (isset($this->rolesTemplateRecords)) {
			$control->setTemplateFile($this->rolesTemplateRecords, 'records');
		}
		return $control;
	}


	/**
	 * @throws FileNotFoundException
	 */
	protected function createComponentResourcesControl(): ResourcesControl
	{
		$control = $this->resourcesControl;
		if (isset($this->resourcesTemplateAdd)) {
			$control->setTemplateFile($this->resourcesTemplateAdd);
		}
		if (isset($this->resourcesTemplateRecords)) {
			$control->setTemplateFile($this->resourcesTemplateRecords, 'records');
		}
		return $control;
	}


	protected function createComponentPrivilegesControl(): PrivilegesControl
	{
		$control = $this->privilegesControl;
		if (isset($this->privilegesTemplateAdd)) {
			$control->setTemplateFile($this->privilegesTemplateAdd);
		}
		if (isset($this->privilegesTemplateRecords)) {
			$control->setTemplateFile($this->privilegesTemplateRecords, 'records');
		}
		return $control;
	}


	/**
	 * @throws FileNotFoundException
	 */
	protected function createComponentPermissionsControl(): PermissionsControl
	{
		$control = $this->permissionsControl;
		if (isset($this->permissionsTemplateAdd)) {
			$control->setTemplateFile($this->permissionsTemplateAdd);
		}
		if (isset($this->permissionsTemplateRecords)) {
			$control->setTemplateFile($this->permissionsTemplateRecords, 'records');
		}
		return $control;
	}


	protected function createComponentResetControl(): ResetControl
	{
		return $this->resetControl;
	}
}
