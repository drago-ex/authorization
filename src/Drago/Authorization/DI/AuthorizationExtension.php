<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\DI;

use Drago\Authorization\Control\PermissionsControl;
use Drago\Authorization\Control\PrivilegesControl;
use Drago\Authorization\Control\ResetControl;
use Drago\Authorization\Control\ResourcesControl;
use Drago\Authorization\Control\RolesControl;
use Drago\Authorization\ExtraPermission;
use Drago\Authorization\Repository\PermissionsRepository;
use Drago\Authorization\Repository\PermissionsRolesViewRepository;
use Drago\Authorization\Repository\PermissionsViewRepository;
use Drago\Authorization\Repository\PrivilegesRepository;
use Drago\Authorization\Repository\ResourcesRepository;
use Drago\Authorization\Repository\RolesRepository;
use Nette\Caching\Cache;
use Nette\DI\CompilerExtension;


class AuthorizationExtension extends CompilerExtension
{
	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();

		/** Authorization cache. */
		$builder->addDefinition($this->prefix('cache'))
			->setFactory(Cache::class)
			->setArguments(['@Nette\Caching\IStorage', 'drago.authorization'])
			->setAutowired(false);

		/** Authorization control. */
		$builder->addDefinition($this->prefix('rolesControl'))
			->setFactory(RolesControl::class)
			->setArguments(['@authorization.cache']);

		$builder->addDefinition($this->prefix('resourceControl'))
			->setFactory(ResourcesControl::class)
			->setArguments(['@authorization.cache']);

		$builder->addDefinition($this->prefix('privilegeControl'))
			->setFactory(PrivilegesControl::class)
			->setArguments(['@authorization.cache']);

		$builder->addDefinition($this->prefix('permissionControl'))
			->setFactory(PermissionsControl::class)
			->setArguments(['@authorization.cache']);

		$builder->addDefinition($this->prefix('resetControl'))
			->setFactory(ResetControl::class);

		/** Authorization repository. */
		$builder->addDefinition($this->prefix('role'))
			->setFactory(RolesRepository::class);

		$builder->addDefinition($this->prefix('resource'))
			->setFactory(ResourcesRepository::class);

		$builder->addDefinition($this->prefix('privilege'))
			->setFactory(PrivilegesRepository::class);

		$builder->addDefinition($this->prefix('permission'))
			->setFactory(PermissionsRepository::class);

		$builder->addDefinition($this->prefix('permission.view'))
			->setFactory(PermissionsViewRepository::class);

		$builder->addDefinition($this->prefix('permission.view.roles'))
			->setFactory(PermissionsRolesViewRepository::class);

		/** Authorization setup. */
		$builder->addDefinition($this->prefix('authorization'))
			->setFactory(ExtraPermission::class)
			->setArguments(['@authorization.cache']);

		$builder->addDefinition($this->prefix('authorization.up'))
			->setFactory('@authorization.authorization::create');
	}
}
