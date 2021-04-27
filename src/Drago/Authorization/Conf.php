<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */


declare(strict_types=1);

namespace Drago\Authorization;

use Exception;


/**
 * Default setting for ACL.
 */
class Conf
{
	/**
	 * Default role.
	 */
	public const ROLE_GUEST = 'guest';
	public const ROLE_MEMBER = 'member';
	public const ROLE_ADMIN = 'admin';


	/**
	 * Option to specify privileges for all actions and signals.
	 */
	public const PRIVILEGE_ALL = '*all';


	/**
	 * Acl cache.
	 */
	public const CACHE = 'drago.aclCache';


	/** @var array|string[] */
	public static array $roles = [
		self::ROLE_GUEST => self::ROLE_GUEST,
		self::ROLE_MEMBER => self::ROLE_MEMBER,
		self::ROLE_ADMIN => self::ROLE_ADMIN,
	];


	/**
	 * @throws Exception
	 */
	final public function __construct()
	{
		throw new Exception('Cannot instantiate static class ' . self::class);
	}
}
