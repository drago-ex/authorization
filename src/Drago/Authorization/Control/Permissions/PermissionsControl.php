<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Permissions;

use Dibi\Exception;
use Drago\Application\UI\Alert;
use Drago\Attr\AttributeDetectionException;
use Drago\Authorization\Conf;
use Drago\Authorization\Control\Base;
use Drago\Authorization\Control\Component;
use Drago\Authorization\Control\Privileges\PrivilegesEntity;
use Drago\Authorization\Control\Privileges\PrivilegesRepository;
use Drago\Authorization\Control\Resources\ResourcesData;
use Drago\Authorization\Control\Resources\ResourcesEntity;
use Drago\Authorization\Control\Resources\ResourcesRepository;
use Drago\Authorization\Control\Roles\RolesEntity;
use Drago\Authorization\Control\Roles\RolesRepository;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\Forms\Controls\BaseControl;
use Nette\SmartObject;
use Throwable;


/**
 * @property-read PermissionsTemplate $template;
 */
class PermissionsControl extends Component implements Base
{
	use SmartObject;

	public string $snippetFactory = 'permissions';
	public string $snippetItems = 'permissionsItems';


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
		$template->setFile($this->templateFactory ?: __DIR__ . '/Permissions.latte');
		$template->form = $this['factory'];
		$template->render();
	}


	/**
	 * @throws Exception
	 * @throws AttributeDetectionException
	 */
	public function renderItems(): void
	{
		$template = $this->template;
		$template->setFile($this->templateItems ?: __DIR__ . '/PermissionsItems.latte');
		$template->roles = $this->permissionsRolesViewRepository->getAll();
		$template->permissions = $this->permissionsViewRepository->getAll();
		$template->deleteId = $this->deleteId;
		$template->render();
	}


	/**
	 * @throws BadRequestException
	 * @throws AttributeDetectionException
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
				$this->getPresenter()->payload->{$this->snippetFactory} = $this->snippetFactory;
				$this->getPresenter()->redrawControl($this->snippetFactory);
			}
		}
	}


	/**
	 * @throws BadRequestException
	 * @throws Exception
	 * @throws AttributeDetectionException
	 */
	public function handleDelete(int $id): void
	{
		$permission = $this->permissionsRepository->getRecord($id);
		$permission ?: $this->error();
		$this->deleteId = $permission->id;
		if ($this->isAjax()) {
			$this->getPresenter()
				->redrawControl($this->snippetItems);
		}
	}


	/**
	 * @throws Exception
	 * @throws BadRequestException
	 * @throws AttributeDetectionException
	 */
	public function handleDeleteConfirm(int $confirm, int $id): void
	{
		$permission = $this->permissionsRepository->get($id)->fetch();
		$permission ?: $this->error();

		if ($confirm === 1) {
			$this->permissionsRepository->remove($id);
			$this->cache->remove(Conf::CACHE);
			$this->getPresenter()->flashMessage(
				'Permission removed.',
				Alert::DANGER
			);

			$snippets = [
				$this->snippetFactory,
				$this->snippetItems,
				$this->snippetMessage,
			];
			if ($this->isAjax()) {
				foreach ($snippets as $snippet) {
					$this->getPresenter()->redrawControl($snippet);
				}
			}

		} else {
			if ($this->isAjax()) {
				$this->getPresenter()
					->redrawControl($this->snippetItems);
			}
		}
	}


	/**
	 * @throws AttributeDetectionException
	 */
	protected function createComponentFactory(): Form
	{
		$form = new Form;

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

		$form->addSelect(PermissionsData::PRIVILEGE_ID, 'Action or signal', $privileges)
			->setPrompt('Select privilege')
			->setRequired();

		$permission = [
			'Deny',
			'Allow',
		];

		$form->addSelect(PermissionsData::ALLOWED, 'Permission', $permission)
			->setPrompt('Select permission')
			->setRequired();

		$form->addHidden(PermissionsData::ID, 0)
			->addRule($form::INTEGER);

		$form->addSubmit('send', 'Send');
		$form->onSuccess[] = [$this, 'success'];
		return $form;
	}


	public function success(Form $form, PermissionsData $data): void
	{
		try {
			$this->permissionsRepository->put($data->toArray());
			$this->cache->remove(Conf::CACHE);

			$message = $data->id ? 'Permission was updated.' : 'Permission added.';
			$this->getPresenter()->flashMessage($message, Alert::INFO);

			if ($this->isAjax()) {
				if ($data->id) {
					$this->getPresenter()->payload->close = 'close';
				}

				$snippets = [
					$this->snippetFactory,
					$this->snippetItems,
					$this->snippetMessage,
				];
				foreach ($snippets as $snippet) {
					$this->getPresenter()->redrawControl($snippet);
				}
			}

			$form->reset();
			$formId = $form[ResourcesData::ID];
			if ($formId instanceof BaseControl) {
				$formId->setDefaultValue(0)
					->addRule($form::INTEGER);
			}

		} catch (Throwable $e) {
			$message = match ($e->getCode()) {
				1062 => 'This permission is already granted.',
				default => 'Unknown status code.',
			};

			$form->addError($message);
			if ($this->isAjax()) {
				$this->getPresenter()->redrawControl($this->snippetFactory);
				$this->redrawControl($this->snippetError);
			}
		}
	}


	public function handleClickOpen()
	{
		if ($this->isAjax()) {
			$this->getPresenter()->payload->{$this->snippetFactory} = $this->snippetFactory;
			$this->getPresenter()->redrawControl($this->snippetFactory);
		}
	}
}
