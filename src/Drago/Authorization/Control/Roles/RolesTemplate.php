<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Roles;

use Drago\Application\UI\ExtraTemplate;
use Nette\ComponentModel\IComponent;


class RolesTemplate extends ExtraTemplate
{
	public IComponent $form;
	public array $roles;
	public ?int $deleteId;
}
