<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Access;

use Drago\Application\UI\ExtraTemplate;
use Nette\ComponentModel\IComponent;


class AccessTemplate extends ExtraTemplate
{
	public IComponent $form;
	public array $usersRoles;
	public int $deleteId;
}
