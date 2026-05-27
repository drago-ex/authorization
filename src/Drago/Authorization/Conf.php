<?php

declare(strict_types=1);

namespace Drago\Authorization;


/** Default setting for ACL. */
final class Conf
{
	public const string
		RoleGuest = 'guest',
		RoleMember = 'member',
		RoleAdmin = 'admin';

	/** Option to specify privileges for all actions and signals. */
	public const string PrivilegeAll = '*';

	/** Acl cache. */
	public const string Cache = 'drago.aclCache';


	/** @var array<string, string> */
	public static array $roles = [
		self::RoleGuest => self::RoleGuest,
		self::RoleMember => self::RoleMember,
		self::RoleAdmin => self::RoleAdmin,
	];
}
