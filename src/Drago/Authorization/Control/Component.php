<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */


declare(strict_types=1);

namespace Drago\Authorization\Control;

use Drago\Application\UI;
use Drago\Authorization\FileNotFoundException;
use Nette\Localization\Translator;
use Nette\SmartObject;
use stdClass;


/**
 * Base control.
 * @property string $snippetFactory
 * @property string $snippetRecords
 */
abstract class Component extends UI\ExtraControl
{
	use SmartObject;

	public ?Translator $translator = null;
	public ?string $templateAdd = null;
	public ?string $templateRecords = null;
	public int $deleteId = 0;
	protected string $snippetError = 'error';
	protected string $snippetMessage = 'message';
	protected string $snippetPermissions = 'permissions';


	/**
	 * Forces control or its snippet to repaint.
	 */
	public function redrawPresenter(string $snippet = null, bool $redraw = true): void
	{
		$this->presenter->redrawControl($snippet, $redraw);
	}


	/**
	 * Saves the message to template, that can be displayed after redirect.
	 */
	public function flashMessagePresenter($message, string $type = 'info'): stdClass
	{
		return $this->presenter->flashMessage($message, $type);
	}


	/**
	 * @throws FileNotFoundException
	 */
	public function setTemplateFile(string $templateFile, string $type = 'add'): self
	{
		if (!is_file($templateFile)) {
			throw new FileNotFoundException('Template file ' . $templateFile . ' not found.');
		}

		if ($type === 'add') {
			$this->templateAdd = $templateFile;

		} elseif ($type === 'records') {
			$this->templateRecords = $templateFile;
		}

		return $this;
	}


	public function setTranslator(Translator $translator): Translator
	{
		return $this->translator = $translator;
	}
}
