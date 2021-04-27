<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control;

use Nette\Application\UI\Form;
use Nette\ComponentModel\IComponent;


interface Component
{
	/** Render template for factory. */
	public function render(): void;

	/** Render template for data table. */
	public function renderRecords(): void;

	/** Returning factory. */
	public function getFactory(): Form|IComponent;

	/** Signal edit. */
	public function handleEdit(int $id): void;

	/** Signal delete. */
	public function handleDelete(int $id): void;

	/** Signal confirm delete. */
	public function handleDeleteConfirm(int $confirm, int $id): void;
}
