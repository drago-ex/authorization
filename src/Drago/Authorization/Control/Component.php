<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control;

use Drago\Application\UI;
use Drago\Authorization\FileNotFoundException;
use Nette\SmartObject;


/**
 * Base control.
 * @property string $snippetFactory
 * @property string $snippetRecords
 */
abstract class Component extends UI\ExtraControl
{
	use SmartObject;

	public ?string $templateAdd = null;
	public ?string $templateRecords = null;
	public int $deleteId = 0;

	protected string $snippetError = 'error';
	protected string $snippetMessage = 'message';
	protected string $snippetPermissions = 'permissions';


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
}
