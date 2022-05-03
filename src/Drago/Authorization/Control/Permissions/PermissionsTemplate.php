<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Permissions;

use Drago\Application\UI\ExtraTemplate;
use Nette\ComponentModel\IComponent;


class PermissionsTemplate extends ExtraTemplate
{
	public IComponent $form;
	public array $roles;
	public array $permissions;
	public int $deleteId;
}
