<?php

declare(strict_types = 1);

/**
 * Drago Extension
 * Package built on Nette Framework
 */

namespace Drago\Authorization\Control;

use Drago\Application\UI;


/**
 * Base control.
 */
abstract class Base extends UI\Control
{
	private const
		ITEMS = 'items',
		FACTORY = 'factory';


	public function redrawFactory()
	{
		if ($this->isAjax()) {
			$this->redrawControl(self::FACTORY);
		}
	}


	public function redrawComponent(): void
	{
		if ($this->isAjax()) {
			$this->redrawControl(self::ITEMS);
			$this->redrawControl(self::FACTORY);
		}
	}


	public function redrawComponentError(): void
	{
		if ($this->isAjax()) {
			$this->redrawControl(self::FACTORY);
			$this->redrawControl('error');
		}
	}


	public function redrawFlashMessage(): void
	{
		if ($this->isAjax()) {
			$this->presenter->redrawControl('message');
		}
	}
}
