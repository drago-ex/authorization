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
use Drago\Authorization\Entity\RolesEntity;
use Drago\Authorization\Repository\PermissionsRepository;
use Drago\Authorization\Repository\PermissionsRolesViewRepository;
use Drago\Authorization\Repository\PermissionsViewRepository;
use Drago\Authorization\Repository\PrivilegesRepository;
use Drago\Authorization\Repository\ResourcesRepository;
use Drago\Authorization\Repository\RolesRepository;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\Forms\Controls\BaseControl;


class PermissionsControl extends Component implements Base
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
		$template = __DIR__ . '/Templates/Permissions.add.latte';
		$form = $this['factory'];
		$this->setRenderControl($template, $form);
	}


	public function renderRecords(): void
	{
		$template = __DIR__ . '/Templates/Permissions.records.latte';
		$roles = $this->permissionsRolesViewRepository->all();
		$permissions = $this->permissionsViewRepository->all();

		$items = [
			'roles' => $roles,
			'permissions' => $permissions,
			'deleteId' => $this->deleteId,
		];

		$this->setRenderControl($template, items: $items);
	}


	/**
	 * @throws BadRequestException
	 */
	public function handleEdit(int $id): void
	{
		$permission = $this->permissionsRepository->get($id)->fetch();
		$permission ?: $this->error();

		if ($this->getSignal()) {
			$form = $this['factory'];
			$form->setDefaults($permission);

			$buttonSend = $form['send'];
			if ($buttonSend instanceof BaseControl) {
				$buttonSend->setCaption('Edit');
			}

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


	protected function createComponentFactory(): Form
	{
		$form = $this->factory();

		$roles = $this->rolesRepository->all()
			->where(RolesEntity::NAME, '!= ?', Conf::ROLE_ADMIN)
			->fetchPairs(RolesEntity::PRIMARY, RolesEntity::NAME);

		$form->addSelect(PermissionsData::ROLE_ID, 'Role', $roles)
			->setPrompt('Select role')
			->setRequired();

		$resources = $this->resourcesRepository->all()
			->fetchPairs(ResourcesEntity::PRIMARY, ResourcesEntity::NAME);

		$form->addSelect(PermissionsData::RESOURCE_ID, 'Resource', $resources)
			->setPrompt('Select resource')
			->setRequired();

		$privileges = $this->privilegesRepository->all()
			->fetchPairs(PrivilegesEntity::PRIMARY, PrivilegesEntity::NAME);

		$form->addSelect(PermissionsData::PRIVILEGE_ID, 'Privilege', $privileges)
			->setPrompt('Select privilege')
			->setRequired();

		$authorization = [
			'yes' => 'Allowed',
			'no' => 'Not allowed',
		];

		$form->addSelect(PermissionsData::ALLOWED, 'Permission', $authorization)
			->setPrompt('Select permission')
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
		$formId = $form[PermissionsData::ID];
		if ($formId instanceof BaseControl) {
			$formId->setDefaultValue(0)
				->addRule(Form::INTEGER);
		}

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
}
