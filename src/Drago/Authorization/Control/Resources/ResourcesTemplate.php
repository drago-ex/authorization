<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Resources;

use Drago\Application\UI\ExtraTemplate;
use Nette\ComponentModel\IComponent;


class ResourcesTemplate extends ExtraTemplate
{
	public IComponent $form;
	public array $resources;
	public ?int $deleteId;
}
