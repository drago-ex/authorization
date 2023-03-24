<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control;

use Drago\Application\UI;
use Nette\SmartObject;


/**
 * Base control.
 * @property string $snippetFactory
 */
abstract class Component extends UI\ExtraControl
{
	use SmartObject;

	public string $openComponentType = 'offcanvas';
	public ?string $templateControl = null;
	public ?string $templateGrid = null;
	protected string $snippetMessage = 'message';
}
