<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control;

use Dibi\Exception;
use Drago\Application\UI\Alert;
use Drago\Authorization\Conf;
use Drago\Authorization\Data\PrivilegesData;
use Drago\Authorization\Entity\PrivilegesEntity;
use Drago\Authorization\NotAllowedChange;
use Drago\Authorization\Repository\PrivilegesRepository;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Caching\Cache;
use Nette\Forms\Controls\BaseControl;
use Nette\InvalidStateException;
use Nette\Localization\Translator;


class PrivilegesControl extends Component implements Base
{
	public string $snippetFactory = 'privileges';
	public string $snippetRecords = 'privilegesRecords';


	public function __construct(
		private Cache $cache,
		private PrivilegesRepository $repository,
	) {
	}


	public function render(): void
	{
		if ($this->template instanceof Template) {
			$template = $this->template;
			$template->form = $this['factory'];

			$this->templateAdd === null
				? $template->setFile(__DIR__ . '/Templates/Privileges.add.latte')
				: $template->setFile($this->templateAdd);

			if ($this->translator instanceof Translator) {
				$template->setTranslator($this->translator);
			}

			$template->render();

		} else {
			throw new InvalidStateException('Control is without template.');
		}
	}


	public function renderRecords(): void
	{
		if ($this->template instanceof Template) {
			$template = $this->template;
			$template->privileges = $this->repository->all()
				->orderBy(PrivilegesEntity::NAME, 'asc');

			$this->templateRecords === null
				? $template->setFile(__DIR__ . '/Templates/Privileges.records.latte')
				: $template->setFile($this->templateRecords);

			if ($this->translator instanceof Translator) {
				$template->setTranslator($this->translator);
			}

			$template->deleteId = $this->deleteId;
			$template->render();

		} else {
			throw new InvalidStateException('Control is without template.');
		}
	}


	/**
	 * @throws BadRequestException
	 * @throws Exception
	 */
	public function handleEdit(int $id): void
	{
		$privilege = $this->repository->getRecord($id);
		$privilege ?: $this->error();

		try {
			if ($this->repository->isAllowed($privilege->name) && $this->getSignal()) {
				$form = $this['factory'];
				$form->setDefaults($privilege);

				$buttonSend = $form['send'];
				if ($buttonSend instanceof BaseControl) {
					$buttonSend->setCaption('Edit');
				}

				if ($this->isAjax()) {
					$this->redrawPresenter($this->snippetFactory);
				}
			}

		} catch (NotAllowedChange $e) {
			if ($e->getCode() === 1001) {
				$this->flashMessagePresenter('The privilege is not allowed to be updated.', Alert::WARNING);

				if ($this->isAjax()) {
					$this->redrawPresenter($this->snippetMessage);
				}
			}
		}
	}


	/**
	 * @throws BadRequestException
	 * @throws Exception
	 */
	public function handleDelete(int $id): void
	{
		$privilege = $this->repository->getRecord($id);
		$privilege ?: $this->error();
		$this->deleteId = $privilege->id;

		if ($this->isAjax()) {
			$this->redrawPresenter($this->snippetRecords);
		}
	}


	/**
	 * @throws BadRequestException
	 * @throws Exception
	 */
	public function handleDeleteConfirm(int $confirm, int $id): void
	{
		$privilege = $this->repository->getRecord($id);
		$privilege ?: $this->error();

		if ($confirm === 1) {
			try {
				if ($this->repository->isAllowed($privilege->name)) {
					$this->repository->erase($id);
					$this->cache->remove(Conf::CACHE);
					$this->flashMessagePresenter('Privilege deleted.', Alert::DANGER);

					if ($this->isAjax()) {
						$this->redrawPresenter($this->snippetFactory);
						$this->redrawPresenter($this->snippetRecords);
						$this->redrawPresenter($this->snippetMessage);
						$this->redrawPresenter($this->snippetPermissions);
					}
				}
			} catch (NotAllowedChange $e) {
				$message = match ($e->getCode()) {
					1001 => 'The privilege is not allowed to be deleted.',
					1451 => 'The privilege can not be deleted, you must first delete the records that are associated with it.',
					default => 'Unknown status code.',
				};

				$this->flashMessagePresenter($message, Alert::WARNING);
				if ($this->isAjax()) {
					$this->redrawPresenter($this->snippetMessage);
				}
			}

		} else {
			if ($this->isAjax()) {
				$this->redrawPresenter($this->snippetRecords);
			}
		}
	}


	public function createComponentFactory(): Form
	{
		$form = new Form;

		if ($this->translator instanceof Translator) {
			$form->setTranslator($this->translator);
		}

		$form->addText(PrivilegesData::NAME, 'Action or signal')
			->setHtmlAttribute('placeholder', 'Action or signal')
			->setHtmlAttribute('autocomplete', 'off')
			->setRequired();

		$form->addHidden(PrivilegesData::ID, 0)
			->addRule(Form::INTEGER);

		$form->addSubmit('send', 'Send');
		$form->onSuccess[] = [$this, 'success'];
		return $form;
	}


	public function success(Form $form, PrivilegesData $data): void
	{
		try {
			$form->reset();

			$formId = $form[PrivilegesData::ID];
			if ($formId instanceof BaseControl) {
				$formId->setDefaultValue(0)
					->addRule(Form::INTEGER);
			}

			$this->repository->put($data->toArray());
			$this->cache->remove(Conf::CACHE);

			$message = $data->id ? 'Privilege updated.' : 'Privilege inserted.';
			$this->flashMessagePresenter($message);

			if ($this->isAjax()) {
				$this->redrawPresenter($this->snippetFactory);
				$this->redrawPresenter($this->snippetRecords);
				$this->redrawPresenter($this->snippetMessage);
				$this->redrawPresenter($this->snippetPermissions);
			}

		} catch (\Exception $e) {
			$message = match ($e->getCode()) {
				1062 => 'This privilege already exists.',
				default => 'Unknown status code.',
			};

			$form->addError($message);
			if ($this->isAjax()) {
				$this->redrawPresenter($this->snippetFactory);
				$this->redrawControl($this->snippetError);
			}
		}
	}
}
