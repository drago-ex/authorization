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
	public const RoleGuest = 'guest';
	public const RoleMember = 'member';
	public const RoleAdmin = 'admin';

	/**
	 * Option to specify privileges for all actions and signals.
	 */
	public const PrivilegeAll = '*all';

	/**
	 * Acl cache.
	 */
	public const CACHE = 'drago.aclCache';


	/** @var array|string[] */
	public static array $roles = [
		self::RoleGuest => self::RoleGuest,
		self::RoleMember => self::RoleMember,
		self::RoleAdmin => self::RoleAdmin,
	];
}
