<?php

declare(strict_types = 1);

/**
 * Drago Extension
 * Package built on Nette Framework
 */

namespace Drago\Authorization\Control;

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
		$template->items = $this->repository->all();
		$template->form = $this['factory'];
		$template->setFile(__DIR__ . '/../templates/roles/acl.roles.latte');
		$template->render();
	}


	private function factoryItems(): array
	{
		$arr = [];

		/** @var Entity\RolesEntity $item */
		foreach ($this->repository->all() as $item) {
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

		$dataId = (int) $this->getParameter('dataId');
		if ($this->getSignal()) {
			foreach ($this->factoryItems() as $key => $item) {
				if ($dataId !== $key) {
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
			$entity = $this->entity;
			$roleId = (int) $values->roleId;

			if ($values->roleId) {
				$entity->setRoleId($roleId);
				$message = 'Role updated.';
				$type = 'info';
			} else {
				$message = 'The role was inserted.';
				$type = 'success';
			}

			$entity->setName($values->name);
			$entity->setParent($values->parent === null ? 0 : $values->parent);
			$this->repository->save($entity);

			$form->reset();
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
	public function handleEdit(int $dataId): void
	{
		$row = $this->getRecord($dataId);
		try {
			if ($this->repository->isAllowed($row)) {
				if ($this->getSignal()) {
					$form = $this['factory'];
					$form['send']->caption = 'Edit';
					$item = $this->repository->find($dataId);
					$item->parent = $item->parent === 0 ? null : $item->parent;
					$form->setDefaults($item);
				}
				$this->redrawFactory();
			}
		} catch (\Exception $e) {
			if ($e->getCode() === 3) {
				$this->presenter->flashMessage('The role is not allowed to be updated.', 'danger');
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
				if (!$this->repository->findParent($id)) {
					$this->repository->eraseId($id);
					$this->presenter->flashMessage('Role deleted.');
					$this->redrawComponent();
					$this->redrawFlashMessage();
				}
			}
		} catch (\Exception $e) {
			switch ($e->getCode()) {
				case 0002: $message = 'Cannot delete role.'; break;
				case 0003: $message = 'You cannot delete a role, first delete the roles that bind to this role.'; break;
				case 1451: $message = 'You cannot delete a role, first remove the assigned permissions that are associated with this role.'; break;
			}
			$this->presenter->flashMessage($message, 'danger');
			$this->redrawFlashMessage();
		}
	}
}
