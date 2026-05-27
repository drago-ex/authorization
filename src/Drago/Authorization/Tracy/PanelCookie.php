<?php

declare(strict_types=1);

namespace Drago\Authorization\Tracy;

use Nette\Http\Session;
use Nette\Http\SessionSection;


/** Handles the session management for storing and retrieving role-related information. */
class PanelCookie
{
	public string $section = 'roles';
	private SessionSection $sessionSection;


	public function __construct(
		private readonly Session $session,
	) {
		$this->sessionSection = $this->session
			->getSection(self::class);
	}


	/**
	 * Saves the provided role items in the session section.
	 * @param array<int, string> $items
	 */
	public function save(array $items): void
	{
		$this->sessionSection->set($this->section, $items);
	}


	/** Loads the saved role items from the session section. */
	public function load(): mixed
	{
		return $this->sessionSection->get($this->section);
	}


	/** Removes the role data from the session section. */
	public function remove(): void
	{
		$this->sessionSection->remove($this->section);
	}
}
