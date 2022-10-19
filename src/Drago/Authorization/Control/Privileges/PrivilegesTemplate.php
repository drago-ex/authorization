<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Privileges;

use Drago\Application\UI\ExtraTemplate;
use Nette\ComponentModel\IComponent;


class PrivilegesTemplate extends ExtraTemplate
{
	public IComponent $form;
	public array $privileges;
	public ?int $deleteId;
}
