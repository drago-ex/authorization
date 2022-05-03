<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control;

use Nette\Application\UI\Form;
use Nette\Localization\Translator;


/**
 * @property Translator $translator
 */
trait Factory
{
	public function create(): Form
	{
		$form = new Form;
		$form->setTranslator($this->translator);
		return $form;
	}
}
