<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */


declare(strict_types=1);

namespace Drago\Authorization;


/**
 * Default setting for ACL.
 */
final class Conf
{
	/**
	 * Default role.
	 */
	public const roleGuest = 'guest';
	public const roleMember = 'member';
	public const roleAdmin = 'admin';

	/**
	 * Option to specify privileges for all actions and signals.
	 */
	public const privilegeAll = '*all';

	/**
	 * Acl cache.
	 */
	public const cache = 'drago.aclCache';


	/** @var array|string[] */
	public static array $roles = [
		self::roleGuest => self::roleGuest,
		self::roleMember => self::roleMember,
		self::roleAdmin => self::roleAdmin,
	];
}
