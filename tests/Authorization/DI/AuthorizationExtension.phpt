<?php

/**
 * Test: Drago\Authorization\DI\AuthorizationExtension
 */

declare(strict_types=1);

use Drago\Authorization\DI\AuthorizationExtension;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';


class TestAuthorizationExtension extends TestCase
{
	protected Container $container;


	public function __construct(Container $container)
	{
		$this->container = $container;
	}


	private function createContainer(): Container
	{
		$params = $this->container->getParameters();
		$loader = new ContainerLoader($params['tempDir'], true);
		$class = $loader->load(function (Compiler $compiler): void {
			$compiler->addExtension('authorization', new AuthorizationExtension);
		});
		return new $class;
	}

}

(new TestAuthorizationExtension($container))->run();
