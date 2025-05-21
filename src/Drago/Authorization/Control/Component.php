<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 *
 * Base control class that manages rendering and component interaction
 * for modal, offcanvas, and grid components.
 */

declare(strict_types=1);

namespace Drago\Authorization\Control;

use App\Authorization\Control\ComponentTemplate;
use Drago\Application\UI;
use Nette\Application\Attributes\Parameter;
use Nette\Application\UI\Template;
use Nette\SmartObject;


/**
 * Base control class.
 * @property-read string|null $snippetFactory
 * @property-read ComponentTemplate $template
 */
abstract class Component extends UI\ExtraControl
{
	use SmartObject;

	#[Parameter]
	public int $id = 0;

	/** Custom control template */
	public ?string $templateControl = null;

	/** Custom grid template. */
	public ?string $templateGrid = null;

	/** Delete item name. */
	public ?string $deleteItems = null;

	/** Base snippets. */
	protected string $snippetMessage = 'message';
	protected string $snippetDeleteItem = 'delete';
	protected string $snippetDeleteTitle = 'title';


	/**
	 * Creates and prepares the template for rendering.
	 */
	public function createRender(): Template
	{
		$template = $this->template;
		$template->setTranslator($this->translator);
		$template->uniqueComponentOffcanvas = $this->getUniqueIdComponent(self::Offcanvas);
		$template->uniqueComponentModal = $this->getUniqueIdComponent(self::Modal);
		$template->deleteItems = $this->deleteItems;
		return $template;
	}


	/**
	 * Calls the offcanvas component.
	 */
	public function offCanvasComponent(): void
	{
		$component = $this->getUniqueIdComponent(self::Offcanvas);
		$this->getPresenter()->payload->{self::Offcanvas} = $component;
		$this->redrawControl($this->snippetFactory);
	}


	/**
	 * Calls the modal component.
	 */
	public function modalComponent(): void
	{
		$component = $this->getUniqueIdComponent(self::Modal);
		$this->getPresenter()->payload->{self::Modal} = $component;
		$this->redrawControl($this->snippetDeleteItem);

		if ($this->templateControl) {
			$this->redrawControl($this->snippetDeleteTitle);
		} else {
			$this->redrawControl($this->snippetFactory);
		}
	}


	/**
	 * Closes modal or offcanvas component.
	 */
	public function closeComponent(): void
	{
		$this->getPresenter()->payload
			->close = 'close';
	}


	/**
	 * Flash a message on the presenter.
	 */
	public function flashMessageOnPresenter(string|\stdClass|\Stringable $message, string $type = 'info'): void
	{
		$this->getPresenter()
			->flashMessage($message, $type);
	}


	/**
	 * Redraws the snippet message on the presenter.
	 */
	public function redrawMessageOnPresenter(): void
	{
		$this->getPresenter()
			->redrawControl($this->snippetMessage);
	}


	/**
	 * Redraw the delete factory snippet.
	 */
	public function redrawDeleteFactory(): void
	{
		$this->redrawControl($this->snippetDeleteItem);
		if (!$this->templateControl) {
			$this->redrawControl($this->snippetFactory);
		}
	}


	/**
	 * Redraw the grid component.
	 */
	public function redrawGrid(): void
	{
		$grid = $this['grid'];
		assert($grid instanceof DatagridComponent);
		$grid->reload();
	}


	/**
	 * Redraw all necessary parts after a delete action.
	 */
	public function redrawDeleteFactoryAll(): void
	{
		$this->redrawDeleteFactory();
		$this->redrawMessageOnPresenter();
		$this->redrawGrid();
	}


	/**
	 * Redraw the factory with a success message.
	 */
	public function redrawSuccessFactory(): void
	{
		$this->redrawMessageOnPresenter();
		$this->redrawControl($this->snippetFactory);
		$this->redrawGrid();
	}
}
