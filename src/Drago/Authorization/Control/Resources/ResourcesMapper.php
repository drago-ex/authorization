<?php

declare(strict_types=1);

namespace Drago\Authorization\Control\Resources;


/**
 * Trait for mapping resource properties (id, name).
 */
trait ResourcesMapper
{
	public ?int $id;
	public string $name;
}
