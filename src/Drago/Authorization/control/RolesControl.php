<?php

declare(strict_types = 1);

/**
 * Drago AclExtension
 * Package built on Nette Framework
 */

namespace Drago\Authorization\Control;

use Drago\Application\UI\Alert;
use Drago\Authorization\Entity\RolesEntity;
use Drago\Authorization\Repository\RolesRepository;
use Drago\Utils\ExtraArrayHash;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SelectBox;


class RolesControl extends Base implements Acl
{
	private string $snippetFactory = 'roles';
	private string $snippetRecords = 'rolesRecords';
	private RolesRepository $repository;
	public int $deleteId = 0;


	public function __construct(RolesRepository $repository)
	{
		$this->repository = $repository;
	}


	public function render(): void
	{
		$template = $this->template;
		$template->form = $this['factory'];
		$template->setFile(__DIR__ . '/../templates/roles.latte');
		$template->render();
	}


	/**
	 * @throws \Dibi\Exception
	 */
	public function renderRecords(): void
	{
		$template = $this->template;
		$template->roles = $this->getRecords();
		$template->deleteId = $this->deleteId;
		$template->setFile(__DIR__ . '/../templates/roles.records.latte');
		$template->render();
	}


	/**
	 * @throws \Dibi\Exception
	 */
	public function getRecords(): array
	{
		$roles = [];

		/**
		 * @var RolesEntity $role
		 * @var RolesEntity $find
		 */
		foreach ($this->repository->all()->orderBy(RolesEntity::ROLE_ID, 'asc') as $role) {
			$find = $this->repository->discover(RolesEntity::ROLE_ID, $role->parent)->fetch();
			$role->parentName = $find->name ?? null;
			$roles[] = $role;
		}
		return $roles;
	}


	/**
	 * @throws \Dibi\Exception
	 * @throws \Nette\Application\AbortException
	 * @throws \Nette\Application\BadRequestException
	 */
	public function handleEdit(int $id): void
	{
		/** @var RolesEntity $role */
		$role = $this->repository->discoverId($id)->fetch();
		$role ?: $this->error();
		try {
			if ($this->repository->isAllowed($role->name) && $this->getSignal()) {

				/** @var Form $form */
				$form = $this['factory'];
				$form['send']->caption = 'Edit';
				$form->setDefaults($role);
				if ($this->isAjax()) {
					$this->redrawPresenter($this->snippetFactory);
				}
			}

		} catch (\Exception $e) {
			if ($e->getCode() === 1001) {
				$this->flashMessagePresenter('The role is not allowed to be updated.', Alert::WARNING);
				if ($this->isAjax()) {
					$this->redrawPresenter($this->snippetMessage);
				}
			}
		}
	}


	/**
	 * @throws \Dibi\Exception
	 * @throws \Nette\Application\AbortException
	 * @throws \Nette\Application\BadRequestException
	 */
	public function handleDelete(int $id): void
	{
		/** @var RolesEntity $role */
		$role = $this->repository->discoverId($id)->fetch();
		$role ?: $this->error();
		$this->deleteId = $role->roleId;
		if ($this->isAjax()) {
			$this->redrawPresenter($this->snippetRecords);
		}
	}


	/**
	 * @throws \Dibi\Exception
	 * @throws \Nette\Application\AbortException
	 * @throws \Nette\Application\BadRequestException
	 */
	public function handleDeleteConfirm(int $confirm, int $id): void
	{
		/** @var RolesEntity $role */
		$role = $this->repository->discoverId($id)->fetch();
		$role ?: $this->error();
		if ($confirm === 1) {
			try {
				$parent = $this->repository->findParent($id);
				if (!$parent && $this->repository->isAllowed($role->name)) {
					$this->repository->eraseId($id);
					$this->flashMessagePresenter('Role deleted.', Alert::DANGER);
					if ($this->isAjax()) {
						$this->redrawPresenter($this->snippetFactory);
						$this->redrawPresenter($this->snippetRecords);
						$this->redrawPresenter($this->snippetMessage);
						//$this->presenter->redrawControl('permissions');
					}
				}
			} catch (\Exception $e) {
				switch ($e->getCode()) {
					case 1001: $message = 'The role is not allowed to be deleted.'; break;
					case 1002: $message = 'The role cannot be deleted because it is bound to another role.'; break;
					case 1451: $message = 'The role can not be deleted, you must first delete the records that are associated with it.'; break;
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


	/**
	 * @throws \Dibi\Exception
	 */
	protected function createComponentFactory(): Form
	{
		$form = new Form;
		$form->addText(RolesEntity::NAME, 'Role')
			->setHtmlAttribute('placeholder', 'Role name')
			->setHtmlAttribute('autocomplete', 'nope')
			->setRequired();

		if ($this->getSignal()) {
			$id = (int) $this->getParameter('id');
			foreach ($this->getRoles() as $key => $item) {
				if ($id !== $key) {
					$roles[$key] = $item;
				}
			}
		}

		$form->addSelect(RolesEntity::PARENT, 'Parent', $roles ?? $this->getRoles())
			->setPrompt('Select parent')
			->setRequired();

		$form->addHidden(RolesEntity::ROLE_ID, 0)
			->addRule(Form::INTEGER);

		$form->addSubmit('send', 'Send');
		$form->onSuccess[] = [$this, 'success'];
		return $form;
	}


	/**
	 * @throws \Nette\Application\AbortException
	 */
	public function success(Form $form, ExtraArrayHash $arrayHash): void
	{
		/** @var RolesEntity $values */
		$values = $arrayHash;
		try {
			$form->reset();
			$this->repository->put($arrayHash->toArray());

			/** @var SelectBox $parent */
			$parent = $form['parent'];
			$parent->setItems($this->getRoles());

			$message = $values->roleId ? 'Role updated.' : 'The role was inserted.';
			$this->presenter->flashMessage($message);
			if ($this->isAjax()) {
				$this->redrawPresenter($this->snippetFactory);
				$this->redrawPresenter($this->snippetRecords);
				$this->redrawPresenter($this->snippetMessage);
				//$this->presenter->redrawControl('permissions');
			}

		} catch (\Exception $e) {
			if ($e->getCode() === 1062) {
				$form->addError('This role already exists.');
			}
			if ($this->isAjax()) {
				$this->redrawPresenter($this->snippetFactory);
				$this->redrawControl($this->snippetError);
			}
		}
	}


	/**
	 * @throws \Dibi\Exception
	 */
	private function getRoles(): array
	{
		$roles = [];

		/** @var RolesEntity $role */
		foreach ($this->repository->all() as $role) {
			$roles[$role->roleId] = $role->name;
		}
		return $roles;
	}
}
