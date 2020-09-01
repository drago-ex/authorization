<?php

declare(strict_types = 1);

/**
 * Drago AclExtension
 * Package built on Nette Framework
 */

namespace Drago\Authorization\Control;

use Drago\Utils\ExtraArrayHash;
use Nette\Application\UI\Form;


interface Acl
{
	/** render template for factory */
	public function render(): void;

	/** render template for data table */
	public function renderRecords(): void;

	/** signal edit */
	public function handleEdit(int $id): void;

	/** signal delete */
	public function handleDelete(int $id): void;

	/** signal confirm delete */
	public function handleDeleteConfirm(int $confirm, int $id): void;

	/** success form */
	public function success(Form $form, ExtraArrayHash $arrayHash): void;
}
