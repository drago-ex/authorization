<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control;

use Contributte\Datagrid\Column\Action;
use Contributte\Datagrid\Column\Action\Confirmation\StringConfirmation;
use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Exception\DatagridException;
use Contributte\Datagrid\Filter\FilterText;
use Nette\ComponentModel\IContainer;


/**
 * This class extends the Datagrid component to add custom actions and translations
 * for a more user-friendly data grid interface.
 */
class DatagridComponent extends Datagrid
{
	public function __construct(
		public ?IContainer $parent = null,
		public ?string $name = null,
	) {
		parent::__construct($parent, $name);
	}


	/**
	 * Translates the given name.
	 */
	public function translate(string $name): ?string
	{
		return $this->translator
			?->translate($name);
	}


	/**
	 * Translates the filter name.
	 */
	public function translateFilter(string $name): string
	{
		return $this->translator
			?->translate($name) ?? $name;
	}


	/**
	 * Adds a basic column with text filter.
	 */
	public function addColumnBase(string $key, string $name, ?string $column = null): FilterText
	{
		return $this->addColumnText($key, $name, $column)
			->setSortable()
			->setFilterText();
	}


	/**
	 * Adds an edit action.
	 * @throws DatagridException
	 */
	public function addActionEdit(string $key, string $name, ?string $href = null, ?array $params = null): Action
	{
		return $this->addAction($key, $name, $href, $params)
			->setClass('btn btn-xs btn-primary text-white ajax')
			->setDataAttribute('naja-history', 'off');
	}


	/**
	 * Adds a delete action (base).
	 * @throws DatagridException
	 */
	public function addActionDeleteBase(string $key, string $name, ?string $href = null, ?array $params = null): Action
	{
		return $this->addAction($key, $name, $href, $params)
			->setClass('btn btn-xs btn-secondary ajax')
			->setDataAttribute('naja-history', 'off');
	}


	/**
	 * Adds a delete action with confirmation.
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
