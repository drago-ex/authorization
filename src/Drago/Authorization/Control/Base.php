<?php declare(strict_types=1);

namespace Drago\Authorization\Control;


interface Base
{
	function render(): void;

	function handleClickOpenComponent(): void;

	function handleEdit(int $id): void;

	function handleDelete(int $id): void;
}
