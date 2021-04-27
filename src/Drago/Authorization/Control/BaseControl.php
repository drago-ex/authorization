<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */


declare(strict_types=1);

namespace Drago\Authorization\Control;

use Drago\Application\UI;
use Nette\SmartObject;
use stdClass;


/**
 * Base control.
 * @property string $snippetFactory
 * @property string $snippetRecords
 */
abstract class BaseControl extends UI\ExtraControl
{
	use SmartObject;

	public int $deleteId = 0;
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
	public function flashMessagePresenter($message, string $type = 'info'): stdClass
	{
		return $this->presenter->flashMessage($message, $type);
	}
}
