<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Datagrid;

use Contributte\Datagrid\Column\Action;
use Contributte\Datagrid\Column\Action\Confirmation\StringConfirmation;
use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Exception\DatagridException;
use Contributte\Datagrid\Filter\FilterText;
use Nette\ComponentModel\IContainer;


class DatagridComponent extends Datagrid
{
	public function __construct(
		public ?IContainer $parent = null,
		public ?string $name = null,
	) {
		parent::__construct($parent, $name);
	}


	public function translate(string $name): ?string
	{
		return $this->translator
			?->translate($name);

	}


	public function translateFilter(string $name): string
	{
		return $this->translator
			?->translate($name) ?? $name;
	}


	public function addColumnBase(string $key, string $name, ?string $column = null): FilterText
	{
		return $this->addColumnText($key, $name, $column)
			->setSortable()
			->setFilterText();
	}


	/**
	 * @throws DatagridException
	 */
	public function addActionEdit(string $key, string $name, ?string $href = null, ?array $params = null): Action
	{
		return $this->addAction($key, $name, $href, $params)
			->setClass('btn btn-xs btn-primary text-white ajax')
			->setDataAttribute('naja-history', 'off');
	}


	/**
	 * @throws DatagridException
	 */
	public function addActionDelete(string $key, string $name, ?string $href = null, ?array $params = null): Action
	{
		$confirm = 'Are you sure you want to delete the selected item?';
		if ($this->translator) {
			$confirm = $this->translate($confirm);
		}

		return $this->addAction($key, $name, $href, $params)
			->setClass('btn btn-xs btn-danger ajax')
			->setDataAttribute('naja-history', 'off')
			->setConfirmation(new StringConfirmation($confirm));
	}
}
