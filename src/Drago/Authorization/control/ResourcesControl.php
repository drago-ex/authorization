<?php

declare(strict_types = 1);

/**
 * Drago AclExtension
 * Package built on Nette Framework
 */

namespace Drago\Authorization\Control;

use Drago\Application\UI\Alert;
use Drago\Authorization\Entity\ResourcesEntity;
use Drago\Authorization\Repository\PermissionsRepository;
use Drago\Authorization\Repository\ResourcesRepository;
use Drago\Utils\ExtraArrayHash;
use Nette\Application\UI\Form;


class ResourcesControl extends Base implements Acl
{
	private string $snippetFactory = 'resources';
	private string $snippetRecords = 'resourcesRecords';
	private ResourcesRepository $repository;
	private PermissionsRepository $permissionsRepository;
	public int $deleteId = 0;


	public function __construct(ResourcesRepository $repository, PermissionsRepository $permissionsRepository)
	{
		$this->repository = $repository;
		$this->permissionsRepository = $permissionsRepository;
	}


	public function render(): void
	{
		$template = $this->template;
		$template->form = $this['factory'];
		$template->setFile(__DIR__ . '/../templates/resources.latte');
		$template->render();
	}


	public function renderRecords(): void
	{
		$template = $this->template;
		$template->resources = $this->repository->all()
			->orderBy(ResourcesEntity::NAME, 'asc');

		$template->deleteId = $this->deleteId;
		$template->setFile(__DIR__ . '/../templates/resources.records.latte');
		$template->render();
	}


	/**
	 * @throws \Dibi\Exception
	 * @throws \Nette\Application\AbortException
	 * @throws \Nette\Application\BadRequestException
	 */
	public function handleEdit(int $id): void
	{
		/** @var ResourcesEntity $resource */
		$resource = $this->repository->discoverId($id)->fetch();
		$resource ?: $this->error();

		if ($this->getSignal()) {

			/** @var Form $form */
			$form = $this['factory'];
			$form['send']->caption = 'Edit';
			$form->setDefaults($resource);
			if ($this->isAjax()) {
				$this->redrawPresenter($this->snippetFactory);
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
		/** @var ResourcesEntity $resource */
		$resource = $this->repository->discoverId($id)->fetch();
		$resource ?: $this->error();
		$this->deleteId = $resource->resourceId;
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
		/** @var ResourcesEntity $role */
		$resource = $this->repository->discoverId($id)->fetch();
		$resource ?: $this->error();
		if ($confirm === 1) {
			try {
				$this->repository->eraseId($id);
				$this->permissionsRepository->removeCache();
				$this->flashMessagePresenter('Resource deleted.', Alert::DANGER);
				if ($this->isAjax()) {
					$this->redrawPresenter($this->snippetFactory);
					$this->redrawPresenter($this->snippetRecords);
					$this->redrawPresenter($this->snippetMessage);
					$this->redrawPresenter($this->snippetPermissions);
				}

			} catch (\Exception $e) {
				if ($e->getCode() === 1451) {
					$this->flashMessagePresenter('The resource can not be deleted, you must first delete the records that are associated with it', Alert::WARNING);
					if ($this->isAjax()) {
						$this->redrawPresenter($this->snippetMessage);
					}
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
		$form->addText(ResourcesEntity::NAME, 'Source')
			->setHtmlAttribute('placeholder', 'Source name')
			->setHtmlAttribute('autocomplete', 'off')
			->setRequired();

		$form->addHidden(ResourcesEntity::RESOURCE_ID, 0)
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
		/** @var ResourcesEntity $values */
		$values = $arrayHash;
		try {
			$form->reset();
			$this->repository->put($arrayHash->toArray());
			$this->permissionsRepository->removeCache();

			$message = $values->resourceId ? 'Resource updated.' : 'Resource inserted.';
			$this->flashMessagePresenter($message);
			if ($this->isAjax()) {
				$this->redrawPresenter($this->snippetFactory);
				$this->redrawPresenter($this->snippetRecords);
				$this->redrawPresenter($this->snippetMessage);
				$this->redrawPresenter($this->snippetPermissions);
			}

		} catch (\Exception $e) {
			if ($e->getCode() === 1062) {
				$form->addError('This resource already exists.');
			}
			if ($this->isAjax()) {
				$this->redrawPresenter($this->snippetFactory);
				$this->redrawControl($this->snippetError);
			}
		}
	}
}
