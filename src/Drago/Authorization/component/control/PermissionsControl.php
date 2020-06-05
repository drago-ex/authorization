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


class PermissionsControl extends Base
{
	/** @var Entity\PermissionsEntity */
	private $entity;

	/** @var Repository\RolesRepository */
	private $roles;

	/** @var Repository\ResourcesRepository */
	private $resources;

	/** @var Repository\PrivilegesRepository */
	private $privileges;

	/** @var Repository\PermissionsRepository */
	private $permissions;


	public function __construct(
		Entity\PermissionsEntity $entity,
		Repository\RolesRepository $roles,
		Repository\ResourcesRepository $resources,
		Repository\PrivilegesRepository $privileges,
		Repository\PermissionsRepository $permissions)
	{
		$this->entity = $entity;
		$this->roles = $roles;
		$this->resources = $resources;
		$this->privileges = $privileges;
		$this->permissions = $permissions;
	}


	/**
	 * @throws \Dibi\Exception
	 */
	public function render(): void
	{
		$template = $this->template;
		$template->roles = $this->permissions->findRoles();
		$template->items = $this->permissions->getAll();
		$template->form = $this['factory'];
		$template->setFile(__DIR__ . '/../templates/permissions.latte');
		$template->render();
	}


	/**
	 * @return array|Entity\PermissionsEntity|null
	 * @throws \Dibi\Exception
	 * @throws \Nette\Application\BadRequestException
	 */
	private function getRecord(int $id)
	{
		$row = $this->permissions->find($id);
		$row ?: $this->error();
		return $row;
	}


	protected function createComponentFactory(): UI\Form
	{
		$form = new UI\Form;
		$roles = [];
		foreach ($this->roles->all() as $role) {
			$roles[$role->roleId] = $role->name;
		}

		$form->addSelect('roleId', 'Role', $roles)
			->setPrompt('Role selection')
			->setRequired();

		$resources = [];
		foreach ($this->resources->all() as $resource) {
			$resources[$resource->resourceId] = $resource->name;
		}

		$form->addSelect('resourceId', 'Resource', $resources)
			->setPrompt('Resource selection')
			->setRequired();

		$privileges = [];
		foreach ($this->privileges->all() as $privilege) {
			$privileges[$privilege->privilegeId] = $privilege->name;
		}

		$form->addSelect('privilegeId', 'Privilege', $privileges)
			->setPrompt('Privilege selection')
			->setRequired();

		$allowed = [
			'yes' => 'Allowed',
			'no' => 'Not allowed',
		];

		$form->addSelect('allowed', 'Authorization', $allowed)
			->setPrompt('Authorization selection')
			->setRequired();

		$form->addHidden('permissionId');
		$form->addSubmit('send', 'Send');
		$form->onSuccess[] = [$this, 'process'];
		return $form;
	}


	/**
	 * @throws \Dibi\Exception
	 */
	public function process(UI\Form $form): void
	{
		$values = $form->values;
		$id = (int) $values->permissionId;
		$entity = $this->entity;

		if ($id) {
			$entity->setPermissionId($id);
			$message = 'Permission was updated.';
			$type = Alert::INFO;

		} else {
			$message = 'Permission added.';
			$type = Alert::SUCCESS;
		}

		$entity->setRoleId($values->roleId);
		$entity->setResourceId($values->resourceId);
		$entity->setPrivilegeId($values->privilegeId);
		$entity->setAllowed($values->allowed);
		$this->permissions->save($entity);

		$form->reset();
		$this->presenter->flashMessage($message, $type);
		$this->redrawFlashMessage();
		$this->redrawComponent();
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
		$this->getRecord($id);
		$this->permissions->delete($id);
		$this->presenter->flashMessage('Permission removed.', Alert::DANGER);
		$this->redrawComponent();
		$this->redrawFlashMessage();
	}
}
