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


class PrivilegesControl extends Base
{
	/** @var Entity\PrivilegesEntity */
	private $entity;

	/** @var Repository\PrivilegesRepository */
	private $repository;


	public function __construct(Entity\PrivilegesEntity $entity, Repository\PrivilegesRepository $repository)
	{
		$this->entity = $entity;
		$this->repository = $repository;
	}


	public function render(): void
	{
		$template = $this->template;
		$template->rows = $this->repository->getAll();
		$template->form = $this['factory'];
		$template->setFile(__DIR__ . '/../templates/privileges.latte');
		$template->render();
	}


	/**
	 * @return array|Entity\PrivilegesEntity|null
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
		$form->addText('name', 'Action / signal name')
			->setHtmlAttribute('placeholder', 'Action / signal name')
			->setHtmlAttribute('autocomplete', 'off')
			->setRequired();

		$form->addHidden('privilegeId');
		$form->addSubmit('send', 'Send');
		$form->onSuccess[] = [$this, 'process'];
		return $form;
	}


	public function process(UI\Form $form): void
	{
		try {
			$values = $form->values;
			$privilegeId = (int) $values->privilegeId;
			$entity = $this->entity;

			if ($privilegeId) {
				$entity->setPrivilegeId($privilegeId);
				$message = 'Event updated.';
				$type = Alert::INFO;

			} else {
				$message = 'The action has been inserted.';
				$type = Alert::SUCCESS;
			}

			$entity->setName($values->name);
			$this->repository->save($entity);

			$form->reset();
			$this->presenter->flashMessage($message, $type);
			$this->redrawFlashMessage();
			$this->redrawComponent();

		} catch (\Exception $e) {
			if ($e->getCode() === 1062) {
				$form->addError('This event already exists.');
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
		if ($this->getSignal()) {
			$form = $this['factory'];
			$form['send']->caption = 'Edit';
			$form->setDefaults($row);
			$this->redrawFactory();
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
			$this->repository->eraseId($row->privilegeId);
			$this->presenter->flashMessage('The action has been deleted.', Alert::DANGER);
			$this->redrawComponent();
			$this->redrawFlashMessage();

		} catch (\Exception $e) {
			if ($e->getCode() === 1451) {
				$this->flashMessage('The action cannot be deleted, first delete the assigned permissions that are associated with the action.', Alert::WARNING);
				$this->redrawFlashMessage();
			}
		}
	}
}
