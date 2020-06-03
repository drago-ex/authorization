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
	public function redrawItems(): void
	{
		$this->redrawControl('items');
	}


	public function redrawFactory(): void
	{
		$this->redrawControl('factory');
	}


	public function redrawComponent(): void
	{
		if ($this->isAjax()) {
			$this->redrawItems();
			$this->redrawFactory();
		}
	}


	public function redrawComponentError(): void
	{
		if ($this->isAjax()) {
			$this->redrawFactory();
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
