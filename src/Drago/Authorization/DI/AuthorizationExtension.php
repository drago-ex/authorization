<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\DI;

use Drago\Authorization\Control\AccessControl;
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
use Drago\Authorization\Repository\UsersRepository;
use Drago\Authorization\Repository\UsersRolesRepository;
use Drago\Authorization\Repository\UsersRolesViewRepository;
use Drago\Authorization\Tracy\Panel;
use Nette\Caching\Cache;
use Nette\DI\CompilerExtension;
use Nette\PhpGenerator\ClassType;
use Tracy\Bar;


class AuthorizationExtension extends CompilerExtension
{
	/** @var mixed|string|null */
	private mixed $panel;


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

		$builder->addDefinition($this->prefix('accessControl'))
			->setFactory(AccessControl::class);

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

		$builder->addDefinition($this->prefix('usersRepository'))
			->setFactory(UsersRepository::class);

		$builder->addDefinition($this->prefix('usersRolesRepository'))
			->setFactory(UsersRolesRepository::class);

		$builder->addDefinition($this->prefix('usersRolesViewRepository'))
			->setFactory(UsersRolesViewRepository::class);

		/** Authorization setup. */
		$builder->addDefinition($this->prefix('authorization'))
			->setFactory(ExtraPermission::class)
			->setArguments(['@authorization.cache']);

		$builder->addDefinition($this->prefix('authorization.up'))
			->setFactory('@authorization.authorization::create');

		/** Role switch panel */
		$builder = $this->getContainerBuilder();
		$builder->addDefinition($this->prefix('panel'))
			->setFactory(Panel::class);

		$this->panel = $this->getContainerBuilder()
			->getByType(Bar::class);
	}


	public function afterCompile(ClassType $class): void
	{
		$init = $class->getMethods()['initialize'];
		$init->addBody('$this->getService(?)->addPanel($this->getService(?));', [
			$this->panel, $this->prefix('panel'),
		]);
	}
}
