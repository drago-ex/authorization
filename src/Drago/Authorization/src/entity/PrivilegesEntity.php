<?php

declare(strict_types=1);

namespace Drago\Authorization\Entity;

/**
 * @property int $privilegeId
 * @property string $name
 */
class PrivilegesEntity extends \Drago\Database\Entity
{
	public const TABLE = 'privileges';
	public const PRIVILEGE_ID = 'privilegeId';
	public const NAME = 'name';

	/** @var int */
	public $privilegeId;

	/** @var string */
	public $name;


	public function getPrivilegeId(): ?int
	{
		return $this->privilegeId;
	}


	public function setPrivilegeId(int $var)
	{
		$this['privilegeId'] = $var;
	}


	public function getName(): string
	{
		return $this->name;
	}


	public function setName(string $var)
	{
		$this['name'] = $var;
	}
}
