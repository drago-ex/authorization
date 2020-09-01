<?php

declare(strict_types = 1);

/**
 * Drago AclExtension
 * Package built on Nette Framework
 */

namespace Drago\Authorization\Control;

use Drago\Application\UI;


/**
 * Base control.
 * @property-read  int  $deleteId
 */
abstract class Base extends UI\ExtraControl
{
	protected string $snippetError = 'error';
	protected string $snippetMessage = 'message';
	protected string $snippetPermissions = 'permissions';


	/**
	 * Forces control or its snippet to repaint.
	 */
	public function redrawPresenter(string $snippet = null, bool $redraw = true): void
	{
		$this->presenter->redrawControl($snippet, $redraw);
	}


	/**
	 * Saves the message to template, that can be displayed after redirect.
	 */
	public function flashMessagePresenter($message, string $type = 'info'): \stdClass
	{
		return $this->presenter->flashMessage($message, $type);
	}
}
