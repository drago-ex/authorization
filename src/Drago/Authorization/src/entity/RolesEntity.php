<?php

/**
 * This file is auto-generated.
 */

declare(strict_types=1);

namespace Drago\Authorization\Entity;

/**
 * @property int $roleId
 * @property string $name
 * @property int $parent
 */
class RolesEntity extends \Drago\Database\Entity
{
	const TABLE = 'roles';
	public const ROLE_ID = 'roleId';
	public const NAME = 'name';
	public const PARENT = 'parent';

	/** @var int */
	public $roleId;

	/** @var string */
	public $name;

	/** @var int */
	public $parent;


	public function getRoleId(): ?int
	{
		return $this->roleId;
	}


	public function setRoleId(int $var)
	{
		$this['roleId'] = $var;
	}


	public function getName(): string
	{
		return $this->name;
	}


	public function setName(string $var)
	{
		$this['name'] = $var;
	}


	public function getParent(): int
	{
		return $this->parent;
	}


	public function setParent(int $var)
	{
		$this['parent'] = $var;
	}
}
