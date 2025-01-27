<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace App\Authorization\Control;

use Drago\Application\UI\ExtraTemplate;


/**
 * Component template for rendering component-specific data.
 */
class ComponentTemplate extends ExtraTemplate
{
	public string $uniqueComponentOffcanvas;
	public string $uniqueComponentModal;
	public ?string $deleteItems = null;
}
