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
use Drago\Authorization\Data\PermissionsData;
use Drago\Authorization\Entity\PrivilegesEntity;
use Drago\Authorization\Entity\ResourcesEntity;
use Drago\Authorization\Repository\PermissionsRepository;
use Drago\Authorization\Repository\PermissionsRolesViewRepository;
use Drago\Authorization\Repository\PermissionsViewRepository;
use Drago\Authorization\Repository\PrivilegesRepository;
use Drago\Authorization\Repository\ResourcesRepository;
use Drago\Authorization\Repository\RolesRepository;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\ComponentModel\IComponent;


class PermissionsControl extends BaseControl implements Component
{
	public string $snippetFactory = 'permissions';
	public string $snippetRecords = 'permissionsRecords';


	public function __construct(
		private Cache $cache,
		private RolesRepository $rolesRepository,
		private ResourcesRepository $resourcesRepository,
		private PrivilegesRepository $privilegesRepository,
		private PermissionsRepository $permissionsRepository,
		private PermissionsViewRepository $permissionsViewRepository,
		private PermissionsRolesViewRepository $permissionsRolesViewRepository,
	) {
	}


	public function render(): void
	{
		$template = $this->template;
		$template->form = $this->getFactory();
		$template->setFile(__DIR__ . '/Templates/Permissions.add.latte');
		$template->render();
	}


	public function renderRecords(): void
	{
		$template = $this->template;
		$template->roles = $this->permissionsRolesViewRepository->all();
		$template->permissions = $this->permissionsViewRepository->all();
		$template->deleteId = $this->deleteId;
		$template->setFile(__DIR__ . '/Templates/Permissions.records.latte');
		$template->render();
	}


	public function getFactory(): Form|IComponent
	{
		return $this['factory'];
	}


	protected function createComponentFactory(): Form
	{
		$form = new Form;
		$form->addSelect(PermissionsData::ROLE_ID, 'Role', $this->rolesRepository->getRoles())
			->setPrompt('Role selection')
			->setRequired();

		$resources = $this->resourcesRepository->all()
			->fetchPairs(ResourcesEntity::PRIMARY, ResourcesEntity::NAME);

		$form->addSelect(PermissionsData::RESOURCE_ID, 'Resource', $resources)
			->setPrompt('Resource selection')
			->setRequired();

		$privileges = $this->privilegesRepository->all()
			->fetchPairs(PrivilegesEntity::PRIMARY, PrivilegesEntity::NAME);

		$form->addSelect(PermissionsData::PRIVILEGE_ID, 'Privilege', $privileges)
			->setPrompt('Privilege selection')
			->setRequired();

		$authorization = [
			'yes' => 'Allowed',
			'no' => 'Not allowed',
		];

		$form->addSelect(PermissionsData::ALLOWED, 'Component', $authorization)
			->setPrompt('Component selection')
			->setRequired();

		$form->addHidden(PermissionsData::ID, 0)
			->addRule(Form::INTEGER);

		$form->addSubmit('send', 'Send');
		$form->onSuccess[] = [$this, 'success'];
		return $form;
	}


	/**
	 * @throws Exception
	 */
	public function success(Form $form, PermissionsData $data): void
	{
		$form->reset();
		$form[PermissionsData::ID]->setDefaultValue(0)
			->addRule(Form::INTEGER);

		$this->permissionsRepository->put($data->toArray());
		$this->cache->remove(Conf::CACHE);

		$message = $data->role_id ? 'Permission was updated.' : 'Permission added.';
		$this->flashMessagePresenter($message);

		if ($this->isAjax()) {
			$this->redrawPresenter($this->snippetFactory);
			$this->redrawPresenter($this->snippetRecords);
			$this->redrawPresenter($this->snippetMessage);
		}
	}


	/**
	 * @throws BadRequestException
	 */
	public function handleEdit(int $id): void
	{
		$permission = $this->permissionsRepository->get($id)->fetch();
		$permission ?: $this->error();

		if ($this->getSignal()) {
			$form = $this->getFactory();
			$form['send']->caption = 'Edit';
			$form->setDefaults($permission);

			if ($this->isAjax()) {
				$this->redrawPresenter($this->snippetFactory);
			}
		}
	}


	/**
	 * @throws BadRequestException
	 * @throws Exception
	 */
	public function handleDelete(int $id): void
	{
		$permission = $this->permissionsRepository->getRecord($id);
		$permission ?: $this->error();
		$this->deleteId = $permission->id;

		if ($this->isAjax()) {
			$this->redrawPresenter($this->snippetRecords);
		}
	}


	/**
	 * @throws Exception
	 * @throws BadRequestException
	 */
	public function handleDeleteConfirm(int $confirm, int $id): void
	{
		$permission = $this->permissionsRepository->get($id)->fetch();
		$permission ?: $this->error();

		if ($confirm === 1) {
			$this->permissionsRepository->erase($id);
			$this->cache->remove(Conf::CACHE);
			$this->flashMessagePresenter('Permission removed.', Alert::DANGER);

			if ($this->isAjax()) {
				$this->redrawPresenter($this->snippetFactory);
				$this->redrawPresenter($this->snippetRecords);
				$this->redrawPresenter($this->snippetMessage);
			}

		} else {
			if ($this->isAjax()) {
				$this->redrawPresenter($this->snippetRecords);
			}
		}
	}
}
