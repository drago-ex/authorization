<?php

declare(strict_types = 1);

/**
 * Drago Extension
 * Package built on Nette Framework
 */

namespace Drago\Authorization\Control;

use Drago\Application\UI\Alert;
use Drago\Authorization\Entity;
use Drago\Authorization\Repository;
use Nette\Application\UI;


class RolesControl extends Base
{
	/** @var Entity\RolesEntity */
	private $entity;

	/** @var Repository\RolesRepository */
	private $repository;


	public function __construct(Entity\RolesEntity $entity, Repository\RolesRepository $repository)
	{
		$this->entity = $entity;
		$this->repository = $repository;
	}


	public function render(): void
	{
		$template = $this->template;
		$template->rows = $this->getRoles();
		$template->form = $this['factory'];
		$template->setFile(__DIR__ . '/../templates/roles.latte');
		$template->render();
	}


	/**
	 * @throws \Dibi\Exception
	 */
	private function getRoles(): array
	{
		$roles = [];
		foreach ($this->repository->getAll() as $role) {
			$roleParent = $role->parent;
			if ($roleParent > 0) {
				$roleParent = $this->repository->find($roleParent);
				$role->parent = $roleParent->name;
			}
			$role->parent = $role->parent === 0 ? null : $role->parent;
			$roles[] = $role;
		}
		return $roles;
	}


	private function factoryItems(): array
	{
		$arr = [];
		foreach ($this->repository->getAll() as $item) {
			$arr[$item->roleId] = $item->name;
		}
		return $arr;
	}


	/**
	 * @return array|Entity\RolesEntity|null
	 * @throws \Dibi\Exception
	 * @throws \Nette\Application\BadRequestException
	 */
	private function getRecord(int $id)
	{
		$row = $this->repository->find($id);
		$row ?: $this->error();
		return $row;
	}


	protected function createComponentFactory(): UI\Form
	{
		$form = new UI\Form;
		$form->addText('name', 'Role')
			->setHtmlAttribute('placeholder', 'Role name')
			->setHtmlAttribute('autocomplete', 'nope')
			->setRequired();

		if ($this->getSignal()) {
			$id = (int) $this->getParameter('id');
			foreach ($this->factoryItems() as $key => $item) {
				if ($id !== $key) {
					$items[$key] = $item;
				}
			}
		}

		$form->addSelect('parent', 'Parent', $items ?? $this->factoryItems())
			->setPrompt('Select parent');

		$form->addHidden('roleId');
		$form->addSubmit('send', 'Send');
		$form->onSuccess[] = [$this, 'process'];
		return $form;
	}


	public function process(UI\Form $form): void
	{
		try {
			$values = $form->values;
			$roleId = (int) $values->roleId;
			$entity = $this->entity;

			if ($roleId) {
				$entity->setRoleId($roleId);
				$message = 'Role updated.';
				$type = Alert::INFO;

			} else {
				$message = 'The role was inserted.';
				$type = Alert::SUCCESS;
			}

			$entity->setName($values->name);
			$entity->setParent($values->parent === null ? 0 : $values->parent);
			$this->repository->save($entity);

			$form->reset();
			$form['parent']->setItems($this->factoryItems());

			$this->presenter->flashMessage($message, $type);
			$this->redrawFlashMessage();
			$this->redrawComponent();

		} catch (\Exception $e) {
			if ($e->getCode() === 1062) {
				$form->addError('This role already exists.');
			}
			$this->redrawComponentError();
		}
	}


	/**
	 * @throws \Dibi\Exception
	 * @throws \Nette\Application\BadRequestException
	 */
	public function handleEdit(int $id): void
	{
		$row = $this->getRecord($id);
		try {
			if ($this->repository->isAllowed($row)) {
				if ($this->getSignal()) {
					$row->parent = $row->parent === 0 ? null : $row->parent;
					$form = $this['factory'];
					$form['send']->caption = 'Edit';
					$form->setDefaults($row);
					$this->redrawFactory();
				}
			}
		} catch (\Exception $e) {
			if ($e->getCode() === 0003) {
				$this->presenter->flashMessage('The role is not allowed to be updated.', Alert::WARNING);
				$this->redrawFlashMessage();
			}
		}
	}


	/**
	 * @throws \Dibi\Exception
	 * @throws \Nette\Application\BadRequestException
	 */
	public function handleDelete(int $id): void
	{
		$row = $this->getRecord($id);
		try {
			if ($this->repository->isAllowed($row)) {
				$parent = $this->repository->findParent($id);
				if (!$parent) {
					$this->repository->eraseId($id);
					$this->presenter->flashMessage('Role deleted.', Alert::DANGER);
					$this->redrawComponent();
					$this->redrawFlashMessage();
				}
			}
		} catch (\Exception $e) {
			switch ($e->getCode()) {
				case 0002: $message = 'Cannot delete role.'; break;
				case 0003: $message = 'The role is not allowed to be deleted.'; break;
				case 1451: $message = 'You cannot delete a role, first remove the assigned permissions that are associated with this role.'; break;
			}
			$this->presenter->flashMessage($message, Alert::WARNING);
			$this->redrawFlashMessage();
		}
	}
}
