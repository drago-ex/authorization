<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Reset;

use Drago\Application\UI\ExtraControl;
use Drago\Authorization\Control\Access\AccessControl;
use Drago\Authorization\Control\Permissions\PermissionsControl;
use Drago\Authorization\Control\Privileges\PrivilegesControl;
use Drago\Authorization\Control\ResourcesControl;
use Drago\Authorization\Control\RolesControl;
use Nette\Application\UI\Form;


class ResetControl extends ExtraControl
{
	public function __construct(
		public PermissionsControl $permissionsControl,
		public PrivilegesControl $privilegesControl,
		public ResourcesControl $resourcesControl,
		public RolesControl $rolesControl,
		public AccessControl $accessControl,
	) {
	}


	public function render()
	{
		$template = $this->template;
		$template->setFile(__DIR__ . '/Templates/Reset.form.latte');
		$template->render();
	}


	public function handleReset($factoryId): void
	{
		$components = [
			'rolesControl',
			'resourcesControl',
			'privilegesControl',
			'permissionsControl',
			'accessControl',
		];

		foreach ($components as $component) {
			$form = $this->getPresenter()[$component]['factory'] ?? null;
			if ($form instanceof Form) {
				$formElementId = $form->getElementPrototype()
					->getAttribute('id');

				if ($formElementId === $factoryId) {
					if ($this->isAjax()) {
						$snippet = $this->{$component}->snippetFactory;
						$this->getPresenter()->redrawControl($snippet);
					}
				}
			}
		}
	}
}
