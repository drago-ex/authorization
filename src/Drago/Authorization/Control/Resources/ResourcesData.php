<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Resources;

use Drago;


/**
 * Data container for resources.
 * Extends ExtraArrayHash and uses ResourcesMapper for mapping properties.
 */
class ResourcesData extends Drago\Utils\ExtraArrayHash
{
	use ResourcesMapper;
}
