<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control;

use Drago\Application\UI\ExtraControl;
use Nette\Application\UI\Form;


class ResetControl extends ExtraControl
{
	public function __construct(
		private PermissionsControl $permissionsControl,
		private PrivilegesControl $privilegesControl,
		private ResourcesControl $resourcesControl,
		private RolesControl $rolesControl,
		private AccessControl $accessControl,
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
