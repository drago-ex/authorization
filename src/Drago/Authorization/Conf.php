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
	public const string RoleGuest = 'guest';
	public const string RoleMember = 'member';
	public const string RoleAdmin = 'admin';

	/**
	 * Option to specify privileges for all actions and signals.
	 */
	public const string PrivilegeAll = '*';

	/**
	 * Acl cache.
	 */
	public const string Cache = 'drago.aclCache';


	/** @var string[] Array of roles. */
	public static array $roles = [
		self::RoleGuest => self::RoleGuest,
		self::RoleMember => self::RoleMember,
		self::RoleAdmin => self::RoleAdmin,
	];
}
