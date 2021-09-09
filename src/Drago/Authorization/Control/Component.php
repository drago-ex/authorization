<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */


declare(strict_types=1);

namespace Drago\Authorization\Control;

use Drago\Application\UI;
use Drago\Authorization\FileNotFoundException;
use Nette\Application\UI\Form;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\InvalidStateException;
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


	public function multipleRedrawPresenter(array $snippets): void
	{
		foreach ($snippets as $snippet) {
			$this->redrawPresenter($snippet);
		}
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
	public function setTemplateFile(string $templateFile, string $type = 'add'): void
	{
		if (!is_file($templateFile)) {
			throw new FileNotFoundException('Template file ' . $templateFile . ' not found.');
		}

		if ($type === 'add') {
			$this->templateAdd = $templateFile;

		} elseif ($type === 'records') {
			$this->templateRecords = $templateFile;
		}
	}


	public function setTranslator(Translator $translator): Translator
	{
		return $this->translator = $translator;
	}


	public function setRenderControl(string $templateFile, ?Form $form = null, ?array $items = []): void
	{
		if ($this->template instanceof Template) {
			$template = $this->template;

			if ($form instanceof Form) {
				$template->form = $form;
			}

			if (is_array($items)) {
				foreach ($items as $key => $item) {
					$template->$key = $item;
				}
			}

			if ($this->translator instanceof Translator) {
				$template->setTranslator($this->translator);
			}

			$template->setFile($templateFile);
			$template->render();

		} else {
			throw new InvalidStateException('Incorrect instance type.');
		}
	}


	public function factory(): Form
	{
		$form = new Form;
		if ($this->translator instanceof Translator) {
			$form->setTranslator($this->translator);
		}
		return $form;
	}
}
