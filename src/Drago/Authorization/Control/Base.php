<?php

declare(strict_types=1);

namespace Drago\Authorization\Control;


interface Base
{
	public function render(): void;

	public function handleClickOpenComponent(): void;

	public function handleEdit(int $id): void;

	public function handleDelete(int $id): void;
}
