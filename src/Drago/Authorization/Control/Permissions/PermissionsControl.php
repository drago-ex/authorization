<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Permissions;

use Dibi\Exception;
use Drago\Application\UI\Alert;
use Drago\Authorization\Conf;
use Drago\Authorization\Control\Base;
use Drago\Authorization\Control\Component;
use Drago\Authorization\Service\Data\PermissionsData;
use Drago\Authorization\Service\Entity\PrivilegesEntity;
use Drago\Authorization\Service\Entity\ResourcesEntity;
use Drago\Authorization\Service\Entity\RolesEntity;
use Drago\Authorization\Service\Repository\PermissionsRepository;
use Drago\Authorization\Service\Repository\PermissionsRolesViewRepository;
use Drago\Authorization\Service\Repository\PermissionsViewRepository;
use Drago\Authorization\Service\Repository\PrivilegesRepository;
use Drago\Authorization\Service\Repository\ResourcesRepository;
use Drago\Authorization\Service\Repository\RolesRepository;
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
		$template = $this->templateAdd ?: $template;
		$items = [
			'form' => $this['factory'],
		];
		$this->setRenderControl($template, $items);
	}


	public function renderRecords(): void
	{
		$template = __DIR__ . '/Templates/Permissions.records.latte';
		$template = $this->templateRecords ?: $template;
		$roles = $this->permissionsRolesViewRepository->all();
		$permissions = $this->permissionsViewRepository->all();

		$items = [
			'roles' => $roles,
			'permissions' => $permissions,
			'deleteId' => $this->deleteId,
		];

		$this->setRenderControl($template, $items);
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
			if ($form instanceof Form) {
				$form->setDefaults($permission);
			}

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
				$this->multipleRedrawPresenter([
					$this->snippetFactory,
					$this->snippetRecords,
					$this->snippetMessage,
				]);
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
			'Deny',
			'Allowed',
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

		try {
			$this->permissionsRepository->put($data->toArray());
			$this->cache->remove(Conf::CACHE);

			$message = $data->id ? 'Permission was updated.' : 'Permission added.';
			$this->flashMessagePresenter($message);
		} catch (\Throwable $e) {
			$message = match ($e->getCode()) {
				1062 => 'This permission is already granted.',
				default => 'Unknown status code.',
			};

			$form->addError($message);
		}

		if ($this->isAjax()) {
			$this->multipleRedrawPresenter([
				$this->snippetFactory,
				$this->snippetRecords,
				$this->snippetMessage,
			]);
		}
	}
}
