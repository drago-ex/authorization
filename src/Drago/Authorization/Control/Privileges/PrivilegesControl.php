<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Privileges;

use Dibi\Exception;
use Drago\Application\UI\Alert;
use Drago\Authorization\Conf;
use Drago\Authorization\Control\Base;
use Drago\Authorization\Control\Component;
use Drago\Authorization\NotAllowedChange;
use Drago\Authorization\Service\Data\PrivilegesData;
use Drago\Authorization\Service\Entity\PrivilegesEntity;
use Drago\Authorization\Service\Repository\PrivilegesRepository;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\Forms\Controls\BaseControl;


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
		$template = __DIR__ . '/Templates/Privileges.add.latte';
		$template = $this->templateAdd ?: $template;
		$items = [
			'form' => $this['factory'],
		];
		$this->setRenderControl($template, $items);
	}


	public function renderRecords(): void
	{
		$template = __DIR__ . '/Templates/Privileges.records.latte';
		$template = $this->templateRecords ?: $template;
		$privileges = $this->repository->all()
			->orderBy(PrivilegesEntity::NAME, 'asc')
			->fetchAll();

		$items = [
			'privileges' => $privileges,
			'deleteId' => $this->deleteId,
		];

		$this->setRenderControl($template, $items);
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
				if ($form instanceof Form) {
					$form->setDefaults($privilege);
				}

				$buttonSend = $form['send'];
				if ($buttonSend instanceof BaseControl) {
					$buttonSend->setCaption('Edit');
				}

				if ($this->isAjax()) {
					$this->presenter->payload->{$this->snippetFactory} = $this->snippetFactory;
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
						$this->multipleRedrawPresenter([
							$this->snippetFactory,
							$this->snippetRecords,
							$this->snippetMessage,
							$this->snippetPermissions,
						]);
					}
				}
			} catch (\Throwable $e) {
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
		$form = $this->factory();
		$form->addText(PrivilegesData::NAME, 'Action or signal')
			->setHtmlAttribute('placeholder', 'Name action or signal')
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
				$this->multipleRedrawPresenter([
					$this->snippetFactory,
					$this->snippetRecords,
					$this->snippetMessage,
					$this->snippetPermissions,
				]);
			}

		} catch (\Throwable $e) {
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


	public function handleClickOpen()
	{
		if ($this->isAjax()) {
			$this->presenter->payload->{$this->snippetFactory} = $this->snippetFactory;
			$this->redrawPresenter($this->snippetFactory);
		}
	}
}
