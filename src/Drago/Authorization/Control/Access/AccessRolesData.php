<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Access;

use Drago;


/**
 * Data class for Access roles.
 */
class AccessRolesData extends Drago\Utils\ExtraArrayHash
{
	// Constant for the ID field
	public const string Id = 'id';

	public array $role_id;
	public ?int $user_id;
	public ?int $id;
}
