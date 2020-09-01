<?php

declare(strict_types=1);

/**
 * Drago AclExtension
 * Package built on Nette Framework
 */

namespace Drago\Authorization;

use Exception;


/**
 * Default setting for ACL.
 */
class Auth
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
	public const ACL_CACHE = 'drago.aclCache';


	/**
	 * @throws Exception
	 */
	final public function __construct()
	{
		throw new Exception('Cannot instantiate static class ' . __CLASS__);
	}
}
