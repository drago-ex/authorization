<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Repository;

use Drago\Attr\Table;
use Drago\Authorization\Entity\PermissionsRolesViewEntity;
use Drago\Database\Connect;
use Drago\Database\Repository;


#[Table(PermissionsRolesViewEntity::TABLE)]
class PermissionsRolesViewRepository extends Connect
{
}
