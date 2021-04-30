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
use Drago\Authorization\Data\RolesData;
use Drago\Authorization\NotAllowedChange;
use Drago\Authorization\Repository\RolesRepository;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SelectBox;


class RolesControl extends Component implements Base
{
	public string $snippetFactory = 'roles';
	public string $snippetRecords = 'rolesRecords';


	public function __construct(
		private Cache $cache,
		private RolesRepository $repository,
	) {
	}


	public function render(): void
	{
		$template = $this->template;
		$template->form = $this['factory'];
		$template->setFile(__DIR__ . '/Templates/Roles.add.latte');
		$template->render();
	}


	/**
	 * @throws Exception
	 */
	public function renderRecords(): void
	{
		$template = $this->template;
		$template->roles = $this->getRecords();
		$template->deleteId = $this->deleteId;
		$template->setFile(__DIR__ . '/Templates/Roles.records.latte');
		$template->render();
	}


	public function createComponentFactory(): Form
	{
		$form = new Form;
		$form->addText(RolesData::NAME, 'Role')
			->setHtmlAttribute('placeholder', 'Role name')
			->setHtmlAttribute('autocomplete', 'nope')
			->setRequired();

		if ($this->getSignal()) {
			$id = (int) $this->getParameter('id');
			foreach ($this->repository->getRoles() as $key => $item) {
				if ($id !== $key) {
					$roles[$key] = $item;
				}
			}
		}

		$form->addSelect(RolesData::PARENT, 'Parent', $roles ?? $this->repository->getRoles())
			->setPrompt('Select parent')
			->setRequired();

		$form->addHidden(RolesData::ID, 0)
			->addRule(Form::INTEGER);

		$form->addSubmit('send', 'Send');
		$form->onSuccess[] = [$this, 'success'];
		return $form;
	}


	public function success(Form $form, RolesData $data): void
	{
		try {
			$form->reset();

			/** @var Form|BaseControl $formId */
			$formId = $form[RolesData::ID];
			$formId->setDefaultValue(0)
				->addRule(Form::INTEGER);

			$this->repository->put($data->toArray());
			$this->cache->remove(Conf::CACHE);

			/** @var Form|SelectBox $parent */
			$parent = $this['factory']['parent'];
			$parent->setItems($this->repository->getRoles());

			$message = $data->id ? 'Role updated.' : 'The role was inserted.';
			$this->flashMessagePresenter($message);

			if ($this->isAjax()) {
				$this->redrawPresenter($this->snippetFactory);
				$this->redrawPresenter($this->snippetRecords);
				$this->redrawPresenter($this->snippetMessage);
				$this->redrawPresenter($this->snippetPermissions);
			}

		} catch (\Exception $e) {
			$message = match ($e->getCode()) {
				1062 => 'This role already exists.',
				default => 'Unknown status code.',
			};

			$form->addError($message);
			if ($this->isAjax()) {
				$this->redrawPresenter($this->snippetFactory);
				$this->redrawControl($this->snippetError);
			}
		}
	}


	/**
	 * @throws Exception
	 */
	public function getRecords(): array
	{
		$roles = [];
		foreach ($this->repository->getAll() as $role) {
			$parent = $this->repository->findByParent($role->parent);
			$role->parent = $parent->name ?? 'none';
			$roles[] = $role;
		}
		return $roles;
	}


	/**
	 * @throws Exception
	 * @throws BadRequestException
	 */
	public function handleEdit(int $id): void
	{
		$role = $this->repository->getRole($id);
		$role ?: $this->error();

		try {
			if ($this->repository->isAllowed($role->name) && $this->getSignal()) {

				/** @var Form|BaseControl $form */
				$form = $this['factory'];
				$form['send']->caption = 'Edit';
				$form->setDefaults($role);

				if ($this->isAjax()) {
					$this->redrawPresenter($this->snippetFactory);
				}
			}

		} catch (NotAllowedChange $e) {
			if ($e->getCode() === 1001) {
				$this->flashMessagePresenter('The role is not allowed to be updated.', Alert::WARNING);

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
		$role = $this->repository->getRole($id);
		$role ?: $this->error();
		$this->deleteId = $role->id;

		if ($this->isAjax()) {
			$this->redrawPresenter($this->snippetRecords);
		}
	}


	/**
	 * @throws BadRequestException|Exception
	 */
	public function handleDeleteConfirm(int $confirm, int $id): void
	{
		$role = $this->repository->getRole($id);
		$role ?: $this->error();

		if ($confirm === 1) {
			try {
				$parent = $this->repository->findParent($id);

				if (!$parent && $this->repository->isAllowed($role->name)) {
					$this->repository->erase($id);
					$this->cache->remove(Conf::CACHE);
					$this->flashMessagePresenter('Role deleted.', Alert::DANGER);

					if ($this->isAjax()) {
						$this->redrawPresenter($this->snippetFactory);
						$this->redrawPresenter($this->snippetRecords);
						$this->redrawPresenter($this->snippetMessage);
						$this->redrawPresenter($this->snippetPermissions);
					}
				}
			} catch (NotAllowedChange $e) {
				$message = match ($e->getCode()) {
					1001 => 'The role is not allowed to be deleted.',
					1002 => 'The role cannot be deleted because it is bound to another role.',
					1451 => 'The role can not be deleted, you must first delete the records that are associated with it.',
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
}
