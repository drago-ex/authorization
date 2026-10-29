<?php declare(strict_types=1);

/**
 * Drago Extension
 * Package built on Nette Framework
 */

namespace Drago\Authorization\Control\Resources;

use Drago;


/**
 * Data container for resources.
 * Extends ExtraArrayHash and uses ResourcesMapper for mapping properties.
 */
class ResourcesValues extends Drago\Utils\ExtraArrayHash
{
	use ResourcesMapper;
}
