<?php

declare(strict_types = 1);

/**
 * Drago AclExtension
 * Package built on Nette Framework
 */

namespace Drago\Authorization\Control;

use Drago\Application\UI\Alert;
use Drago\Authorization\Entity\PrivilegesEntity;
use Drago\Authorization\Repository\PrivilegesRepository;
use Drago\Utils\ExtraArrayHash;
use Nette\Application\UI\Form;


class PrivilegesControl extends Base implements Acl
{
	private string $snippetFactory = 'privileges';
	private string $snippetRecords = 'privilegesRecords';
	private PrivilegesRepository $repository;
	public int $deleteId = 0;


	public function __construct(PrivilegesRepository $repository)
	{
		$this->repository = $repository;
	}


	public function render(): void
	{
		$template = $this->template;
		$template->form = $this['factory'];
		$template->setFile(__DIR__ . '/../templates/privileges.latte');
		$template->render();
	}


	public function renderRecords(): void
	{
		$template = $this->template;
		$template->privileges = $this->repository->all()
			->orderBy(PrivilegesEntity::NAME, 'asc');

		$template->deleteId = $this->deleteId;
		$template->setFile(__DIR__ . '/../templates/privileges.records.latte');
		$template->render();
	}


	/**
	 * @throws \Dibi\Exception
	 * @throws \Nette\Application\BadRequestException
	 */
	public function handleEdit(int $id): void
	{
		/** @var PrivilegesEntity $privilege */
		$privilege = $this->repository->discoverId($id)->fetch();
		$privilege ?: $this->error();
		try {
			if ($this->repository->isAllowed($privilege->name) && $this->getSignal()) {

				/** @var Form $form */
				$form = $this['factory'];
				$form['send']->caption = 'Edit';
				$form->setDefaults($privilege);
				if ($this->isAjax()) {
					$this->redrawPresenter($this->snippetFactory);
				}
			}

		} catch (\Exception $e) {
			if ($e->getCode() === 1001) {
				$this->flashMessagePresenter('The privilege is not allowed to be updated.', Alert::WARNING);
				if ($this->isAjax()) {
					$this->redrawPresenter($this->snippetMessage);
				}
			}
		}
	}


	/**
	 * @throws \Dibi\Exception
	 * @throws \Nette\Application\BadRequestException
	 */
	public function handleDelete(int $id): void
	{
		/** @var PrivilegesEntity $privilege */
		$privilege = $this->repository->discoverId($id)->fetch();
		$privilege ?: $this->error();
		$this->deleteId = $privilege->privilegeId;
		if ($this->isAjax()) {
			$this->redrawPresenter($this->snippetRecords);
		}
	}


	/**
	 * @throws \Dibi\Exception
	 * @throws \Nette\Application\BadRequestException
	 */
	public function handleDeleteConfirm(int $confirm, int $id): void
	{
		/** @var PrivilegesEntity $privilege */
		$privilege = $this->repository->discoverId($id)->fetch();
		$privilege ?: $this->error();
		if ($confirm === 1) {
			try {
				if ($this->repository->isAllowed($privilege->name)) {
					$this->repository->eraseId($id);
					$this->flashMessagePresenter('Privilege deleted.', Alert::DANGER);
					if ($this->isAjax()) {
						$this->redrawPresenter($this->snippetFactory);
						$this->redrawPresenter($this->snippetRecords);
						$this->redrawPresenter($this->snippetMessage);
						$this->redrawPresenter($this->snippetPermissions);
					}
				}
			} catch (\Exception $e) {
				switch ($e->getCode()) {
					case 1001: $message = 'The privilege is not allowed to be deleted.'; break;
					case 1451: $message = 'The privilege can not be deleted, you must first delete the records that are associated with it.'; break;
				}
				$this->flashMessagePresenter($message ?? '', Alert::WARNING);
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


	protected function createComponentFactory(): Form
	{
		$form = new Form;
		$form->addText(PrivilegesEntity::NAME, 'Action / signal name')
			->setHtmlAttribute('placeholder', 'Action / signal name')
			->setHtmlAttribute('autocomplete', 'off')
			->setRequired();

		$form->addHidden(PrivilegesEntity::PRIVILEGE_ID, 0)
			->addRule(Form::INTEGER);

		$form->addSubmit('send', 'Send');
		$form->onSuccess[] = [$this, 'success'];
		return $form;
	}


	public function success(Form $form, ExtraArrayHash $arrayHash): void
	{
		/** @var PrivilegesEntity $values */
		$values = $arrayHash;
		try {
			$form->reset();
			$this->repository->put($arrayHash->toArray());

			$message = $values->privilegeId ? 'Privilege updated.' : 'Privilege inserted.';
			$this->flashMessagePresenter($message);
			if ($this->isAjax()) {
				$this->redrawPresenter($this->snippetFactory);
				$this->redrawPresenter($this->snippetRecords);
				$this->redrawPresenter($this->snippetMessage);
				$this->redrawPresenter($this->snippetPermissions);
			}

		} catch (\Exception $e) {
			if ($e->getCode() === 1062) {
				$form->addError('This privilege already exists.');
			}
			if ($this->isAjax()) {
				$this->redrawPresenter($this->snippetFactory);
				$this->redrawControl($this->snippetError);
			}
		}
	}
}
