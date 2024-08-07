<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Datagrid;

use Contributte\Datagrid\Datagrid;
use Nette\ComponentModel\IContainer;
use Nette\Localization\Translator;


class DatagridComponent extends Datagrid
{
	public function __construct(
		?IContainer $parent = null, 
		?string $name = null, 
		?Translator $translator = null,
	) {
		parent::__construct($parent, $name);
	}


	private function translate(string $name): string
	{
		return $this->translator
			->translate($name);
	}
}
