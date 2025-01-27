<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Tracy;

use Nette\Http\Session;
use Nette\Http\SessionSection;


/**
 * PanelCookie handles the session management for storing and retrieving role-related information.
 * It allows saving, loading, and removing role data in a session section.
 */
class PanelCookie
{
	public string $section = 'roles'; // Session section name for storing role data.
	private SessionSection $sessionSection; // Session section instance for roles data.


	public function __construct(
		private readonly Session $session, // Nette session service for session management.
	) {
		// Initialize the session section for this class.
		$this->sessionSection = $this->session
			->getSection(self::class);
	}


	/**
	 * Saves the provided role items in the session section.
	 */
	public function save(array $items): void
	{
		$this->sessionSection->set($this->section, $items);
	}


	/**
	 * Loads the saved role items from the session section.
	 *
	 * @return mixed The saved role data or null if not set.
	 */
	public function load(): mixed
	{
		return $this->sessionSection->get($this->section);
	}


	/**
	 * Removes the role data from the session section.
	 */
	public function remove(): void
	{
		$this->sessionSection->remove($this->section);
	}
}
