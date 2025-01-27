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
	/**
	 * Panel for role switching
	 * Stores the panel instance to add it to Tracy's debug bar.
	 */
	private mixed $panel;


	/**
	 * Loads the configuration for the extension.
	 * Initializes and registers services from the configuration file and adds the panel definition.
	 */
	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();

		// load the extension configuration file.
		$this->compiler->loadDefinitionsFromConfig(
			$this->loadFromFile(__DIR__ . '/services.neon')['services'],
		);

		/** Role switch panel */
		// Registers the role switch panel definition in the container
		$builder->addDefinition($this->prefix('panel'))
			->setFactory(Panel::class);

		// Retrieve the Tracy debug bar service
		$this->panel = $this->getContainerBuilder()
			->getByType(Bar::class);
	}


	/**
	 * Adds the role switch panel to the Tracy debug bar.
	 * This method is called after the container is compiled to ensure the panel is added.
	 */
	public function afterCompile(ClassType $class): void
	{
		$init = $class->getMethods()['initialize'];

		// Adds the panel to the Tracy debug bar
		$init->addBody('$this->getService(?)->addPanel($this->getService(?));', [
			$this->panel, $this->prefix('panel'),
		]);
	}
}
