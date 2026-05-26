<?php

declare(strict_types=1);

namespace Drago\Authorization\DI;

use Drago\Authorization\Tracy\Panel;
use Nette\DI\CompilerExtension;
use Nette\PhpGenerator\ClassType;
use Tracy\Bar;


class AuthorizationExtension extends CompilerExtension
{
	private mixed $panel;


	/** Register services to the container. */
	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();

		$this->compiler->loadDefinitionsFromConfig(
			$this->loadFromFile(__DIR__ . '/services.neon')['services'],
		);

		$builder->addDefinition($this->prefix('panel'))
			->setFactory(Panel::class);

		$this->panel = $this->getContainerBuilder()
			->getByType(Bar::class);
	}


	/** Adjustments before compilation. */
	public function afterCompile(ClassType $class): void
	{
		$init = $class->getMethods()['initialize'];

		$init->addBody('$this->getService(?)->addPanel($this->getService(?));', [
			$this->panel, $this->prefix('panel'),
		]);
	}
}
