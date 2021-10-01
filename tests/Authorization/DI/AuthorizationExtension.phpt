<?php

/**
 * Test: Drago\Authorization\DI\AuthorizationExtension
 */

declare(strict_types=1);

use Drago\Authorization\DI\AuthorizationExtension;
use Drago\Authorization\ExtraPermission;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Tester\Assert;
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
		$loader = new ContainerLoader($this->container->getParameters()['tempDir'], true);
		$class = $loader->load(function (Compiler $compiler): void {
			$compiler->loadConfig(Tester\FileMock::create('
			services:
				- Nette\Caching\Storages\FileStorage(../../tmp/cache)
				- Nette\Security\User
				- Nette\Http\Request
				- Nette\Http\UrlScript
				- Nette\Application\Application(Nette\Application\IPresenterFactory, Nette\Routing\Router, Nette\Http\IResponse, Nette\Http\IResponse)
				dibi.connection:
					factory: Dibi\Connection([
						driver: mysqli
						host: 127.0.0.1
						username: root
						password: root
						database: test
						lazy: true
					])
			', 'neon'));
			$compiler->addExtension('authorization', new AuthorizationExtension);
		});
		return new $class;
	}


	public function test01(): void
	{
		Assert::type(ExtraPermission::class, $this->createContainer()
			->getByType(ExtraPermission::class));
	}
}

(new TestAuthorizationExtension($container))->run();
