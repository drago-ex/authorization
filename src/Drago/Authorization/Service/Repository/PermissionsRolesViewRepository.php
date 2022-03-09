<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Service\Repository;

use Drago\Attr\Table;
use Drago\Authorization\Service\Entity\PermissionsRolesViewEntity;
use Drago\Database\Connect;


#[Table(PermissionsRolesViewEntity::TABLE)]
class PermissionsRolesViewRepository extends Connect
{
}