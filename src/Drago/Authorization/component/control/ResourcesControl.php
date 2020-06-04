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


/**
 * Resources control.
 */
class ResourcesControl extends Base
{
	/** @var Entity\ResourcesEntity */
	private $entity;

	/** @var Repository\ResourcesRepository */
	private $repository;


	public function __construct(Entity\ResourcesEntity $entity, Repository\ResourcesRepository $repository)
	{
		$this->entity = $entity;
		$this->repository = $repository;
	}


	public function render(): void
	{
		$template = $this->template;
		$template->rows = $this->repository->getAll();
		$template->form = $this['factory'];
		$template->setFile(__DIR__ . '/../templates/resources.latte');
		$template->render();
	}


	/**
	 * @return array|Entity\ResourcesEntity|null
	 * @throws \Dibi\Exception
	 * @throws \Nette\Application\BadRequestException
	 */
	private function getRecord(int $id)
	{
		$row = $this->repository->find($id);
		$row ?: $this->error();
		return $row;
	}


	/**
	 * @throws \Dibi\Exception
	 */
	protected function createComponentFactory(): UI\Form
	{
		$form = new UI\Form;
		$form->addText('name', 'Source')
			->setHtmlAttribute('placeholder', 'Source name')
			->setHtmlAttribute('autocomplete', 'off')
			->setRequired();

		$form->addHidden('resourceId');
		$form->addSubmit('send', 'Send');
		$form->onSuccess[] = [$this, 'process'];
		return $form;
	}


	public function process(UI\Form $form): void
	{
		try {
			$values = $form->values;
			$resourceId = (int) $values->resourceId;
			$entity = $this->entity;

			if ($resourceId) {
				$entity->setResourceId($resourceId);
				$message = 'Source updated';
				$type = 'info';
			} else {
				$message = 'Source inserted.';
				$type = 'success';
			}

			$entity->setName($values->name);
			$this->repository->save($entity);

			$form->reset();
			$this->presenter->flashMessage($message, $type);
			$this->redrawFlashMessage();
			$this->redrawComponent();

		} catch (\Exception $e) {
			if ($e->getCode() === 1062) {
				$form->addError('This resource already exists.');
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
			$this->repository->eraseId($row->resourceId);
			$this->presenter->flashMessage('The source has been deleted.', 'danger');
			$this->redrawComponent();
			$this->redrawFlashMessage();

		} catch (\Exception $e) {
			if ($e->getCode() === 1451) {
				$this->presenter->flashMessage('The resource cannot be deleted, first delete the assigned permissions that bind to this resource.', 'danger');
				$this->redrawFlashMessage();
			}
		}
	}
}
