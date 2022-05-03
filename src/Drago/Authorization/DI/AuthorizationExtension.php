<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\DI;

use Drago\Authorization\Tracy\Panel;
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

		// load the extension configuration file.
		$this->compiler->loadDefinitionsFromConfig(
			$this->loadFromFile(__DIR__ . '/services.neon')['services']
		);

		/** Role switch panel */
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
