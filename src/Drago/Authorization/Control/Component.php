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
 * @property string $snippetItems
 */
abstract class Component extends UI\ExtraControl
{
	use SmartObject;

	public ?string $templateFactory = null;
	public ?string $templateItems = null;
	public ?int $deleteId = 0;

	protected string $snippetMessage = 'message';
	protected string $snippetPermissions = 'permissions';
}
