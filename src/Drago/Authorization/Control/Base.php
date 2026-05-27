<?php

declare(strict_types=1);

namespace Drago\Authorization\Control;


/** Interface defining the basic methods for handling UI components. */
interface Base
{
	/** Render template for factory. */
	public function render(): void;

	/** Open modal or offCanvas. */
	public function handleClickOpenComponent(): void;

	/** Signal edit. */
	public function handleEdit(int $id): void;

	/** Signal delete. */
	public function handleDelete(int $id): void;
}
