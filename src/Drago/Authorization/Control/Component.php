<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control;

use App\Authorization\Control\ComponentTemplate;
use Drago\Application\UI;
use Nette\Application\UI\Template;
use Nette\SmartObject;


/**
 * Base control.
 * @property-read string $snippetFactory
 * @property-read ComponentTemplate $template
 */
abstract class Component extends UI\ExtraControl
{
	use SmartObject;

	public string $componentType = 'offcanvas';
	public ?string $templateControl = null;
	public ?string $templateGrid = null;
	protected string $snippetMessage = 'message';


	/**
	 * Base render.
	 */
	public function createRender(): Template
	{
		$template = $this->template;
		$template->setTranslator($this->translator);
		$template->uniqueComponentId = $this->getUniqueIdComponent($this->componentType);
		return $template;
	}


	/**
	 * Getting a unique id for offCanvas or modal window.
	 */
	public function getUniqueComponent(string $type): string
	{
		return $this->getUniqueIdComponent($type);
	}


	/**
	 * Calling the offcanvas component.
	 */
	public function offCanvasComponent(): void
	{
		$component = $this->getUniqueComponent($this->componentType);
		$this->getPresenter()->payload->{$this->componentType} = $component;
		$this->redrawControl($this->snippetFactory);
	}


	/**
	 * Redraw snippet message.
	 */
	public function redrawControlMessage(): void
	{
		$this->getPresenter()
			->redrawControl($this->snippetMessage);
	}
}
