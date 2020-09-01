<?php

declare(strict_types=1);

namespace Drago\Authorization\Entity;

final class AclEntity extends \Drago\Database\Entity
{
	use \Nette\SmartObject;

	const TABLE = 'acl';
	const ATHORIZATION_ID = 'athorizationId';
	const ROLE_ID = 'roleId';
	const USER_ID = 'userId';

	public int $athorizationId;
	public int $roleId;
	public int $userId;
}
