<?php

declare(strict_types = 1);

/**
 * Drago AclExtension
 * Package built on Nette Framework
 */

namespace Drago\Authorization\Control;

use Drago\Application\UI\Alert;
use Drago\Authorization\Entity\PermissionsEntity;
use Drago\Authorization\Entity\PrivilegesEntity;
use Drago\Authorization\Entity\ResourcesEntity;
use Drago\Authorization\Entity\RolesEntity;
use Drago\Authorization\Repository\PermissionsRepository;
use Drago\Authorization\Repository\PrivilegesRepository;
use Drago\Authorization\Repository\ResourcesRepository;
use Drago\Authorization\Repository\RolesRepository;
use Drago\Utils\ExtraArrayHash;
use Nette\Application\UI\Form;


class PermissionsControl extends Base implements Acl
{
	private string $snippetFactory = 'permissions';
	private string $snippetRecords = 'permissionsRecords';
	private RolesRepository $rolesRepository;
	private ResourcesRepository $resourcesRepository;
	private PrivilegesRepository $privilegesRepository;
	private PermissionsRepository $permissionsRepository;
	public int $deleteId = 0;


	public function __construct(
		RolesRepository $rolesRepository,
		ResourcesRepository $resourcesRepository,
		PrivilegesRepository $privilegesRepository,
		PermissionsRepository $permissionsRepository
	)
	{
		$this->rolesRepository = $rolesRepository;
		$this->resourcesRepository = $resourcesRepository;
		$this->privilegesRepository = $privilegesRepository;
		$this->permissionsRepository = $permissionsRepository;
	}


	public function render(): void
	{
		$template = $this->template;
		$template->form = $this['factory'];
		$template->setFile(__DIR__ . '/../templates/permissions.latte');
		$template->render();
	}


	/**
	 * @throws \Dibi\Exception
	 */
	public function renderRecords(): void
	{
		$template = $this->template;
		$template->roles = $this->permissionsRepository->getRoles();
		$template->permissions = $this->permissionsRepository->getAll();
		$template->deleteId = $this->deleteId;
		$template->setFile(__DIR__ . '/../templates/permissions.records.latte');
		$template->render();
	}


	/**
	 * @throws \Dibi\Exception
	 * @throws \Nette\Application\BadRequestException
	 */
	public function handleEdit(int $id): void
	{
		/** @var PermissionsEntity $permission */
		$permission = $this->permissionsRepository->discoverId($id)->fetch();
		$permission ?: $this->error();

		if ($this->getSignal()) {

			/** @var Form $form */
			$form = $this['factory'];
			$form['send']->caption = 'Edit';
			$form->setDefaults($permission);
			if ($this->isAjax()) {
				$this->redrawPresenter($this->snippetFactory);
			}
		}
	}


	/**
	 * @throws \Dibi\Exception
	 * @throws \Nette\Application\BadRequestException
	 */
	public function handleDelete(int $id): void
	{
		/** @var PermissionsEntity $permission */
		$permission = $this->permissionsRepository->discoverId($id)->fetch();
		$permission ?: $this->error();
		$this->deleteId = $permission->permissionId;
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
		/** @var PermissionsEntity $permission */
		$permission = $this->permissionsRepository->discoverId($id)->fetch();
		$permission ?: $this->error();
		if ($confirm === 1) {
			$this->permissionsRepository->eraseId($id);
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


	protected function createComponentFactory(): Form
	{
		$form = new Form;

		$roles = [];
		/** @var RolesEntity $role */
		foreach ($this->rolesRepository->all() as $role) {
			$roles[$role->roleId] = $role->name;
		}

		$form->addSelect(PermissionsEntity::ROLE_ID, 'Role', $roles)
			->setPrompt('Role selection')
			->setRequired();

		$resources = [];
		/** @var ResourcesEntity $resource */
		foreach ($this->resourcesRepository->all() as $resource) {
			$resources[$resource->resourceId] = $resource->name;
		}

		$form->addSelect(PermissionsEntity::RESOURCE_ID, 'Resource', $resources)
			->setPrompt('Resource selection')
			->setRequired();

		$privileges = [];
		/** @var PrivilegesEntity $privilege */
		foreach ($this->privilegesRepository->all() as $privilege) {
			$privileges[$privilege->privilegeId] = $privilege->name;
		}

		$form->addSelect(PermissionsEntity::PRIVILEGE_ID, 'Privilege', $privileges)
			->setPrompt('Privilege selection')
			->setRequired();

		$authorization = [
			'yes' => 'Allowed',
			'no' => 'Not allowed',
		];

		$form->addSelect(PermissionsEntity::ALLOWED, 'Acl', $authorization)
			->setPrompt('Acl selection')
			->setRequired();

		$form->addHidden(PermissionsEntity::PERMISSION_ID);
		$form->addSubmit('send', 'Send');
		$form->onSuccess[] = [$this, 'success'];
		return $form;
	}


	/**
	 * @throws \Dibi\Exception
	 */
	public function success(Form $form, ExtraArrayHash $arrayHash): void
	{
		/** @var PermissionsEntity $values */
		$values = $arrayHash;
		$form->reset();
		$this->permissionsRepository->put($values->toArray());
		$message = $values->roleId ? 'Permission was updated.' : 'Permission added.';
		$this->flashMessagePresenter($message);
		if ($this->isAjax()) {
			$this->redrawPresenter($this->snippetFactory);
			$this->redrawPresenter($this->snippetRecords);
			$this->redrawPresenter($this->snippetMessage);
		}
	}
}
