<?php

declare(strict_types=1);

namespace Drago\Authorization\Tracy;

use Nette\Http\Session;
use Nette\Http\SessionSection;


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


	public function save(array $items): void
	{
		$this->sessionSection->set($this->section, $items);
	}


	public function load(): mixed
	{
		return $this->sessionSection->get($this->section);
	}


	public function remove(): void
	{
		$this->sessionSection->remove($this->section);
	}
}
