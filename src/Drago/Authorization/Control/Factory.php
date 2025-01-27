<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control;

use Nette\Application\UI\Form;


trait Factory
{
	/**
	 * Creates a new form.
	 */
	public function create(): Form
	{
		$form = new Form;
		$form->setTranslator($this->translator);
		return $form;
	}


	/**
	 * Creates a delete form with a hidden ID field.
	 */
	public function createDelete(int $id): Form
	{
		$form = $this->create();
		$form->addHidden('id', $id)
			->addRule($form::Integer);

		$form->addSubmit('cancel', 'Cancel')->onClick[] = function () {
			$this->redrawDeleteFactory();
		};
		return $form;
	}
}
