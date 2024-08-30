<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Access;

use App\Authorization\Control\ComponentTemplate;
use Contributte\Datagrid\Exception\DatagridException;
use Dibi\DriverException;
use Dibi\Exception;
use Drago\Application\UI\Alert;
use Drago\Attr\AttributeDetectionException;
use Drago\Authorization\Conf;
use Drago\Authorization\Control\Base;
use Drago\Authorization\Control\Component;
use Drago\Authorization\Control\DatagridComponent;
use Drago\Authorization\Control\Factory;
use Drago\Authorization\Control\Roles\RolesEntity;
use Drago\Authorization\Control\Roles\RolesRepository;
use Nette\Application\AbortException;
use Nette\Application\Attributes\Requires;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Security\User;
use Nette\SmartObject;
use Throwable;


/**
 * @property-read ComponentTemplate $template
 */
class AccessControl extends Component implements Base
{
	use SmartObject;
	use Factory;

	public string $snippetFactory = 'access';


	public function __construct(
		private readonly AccessRepository $accessRepository,
		private readonly AccessRolesRepository $accessRolesRepository,
		private readonly AccessRolesViewRepository $accessRolesViewRepository,
		private readonly RolesRepository $rolesRepository,
		private readonly User $user,
	) {
	}


	public function render(): void
	{
		$template = $this->createRender();
		$template->setFile($this->templateControl ?: __DIR__ . '/Access.latte');
		$template->render();
	}


	#[Requires(ajax: true)]
	public function handleClickOpenComponent(): void
	{
		$this->offCanvasComponent();
	}


	protected function createComponentDelete(): Form
	{
		$form = $this->createDelete($this->id);
		$form->addSubmit('confirm', 'Confirm')->onClick[] = function (Form $form, \stdClass $data) {
			$this->accessRolesRepository->delete(AccessRolesEntity::ColumnUserId, $data->id)->execute();
			$this->flashMessageOnPresenter('Access deleted.');
			$this->closeComponent();
			$this->redrawDeleteFactoryAll();
		};
		return $form;
	}


	/**
	 * @throws AttributeDetectionException
	 */
	protected function createComponentFactory(): Form
	{
		$form = $this->create();
		$users = $this->accessRepository->getAllUsers();

		if ($this->getSignal()) {
			$user = $this->accessRepository->getUserById($this->id);
		}

		$form->addSelect(AccessRolesEntity::ColumnUserId, 'User', $user ?? $users)
			->setPrompt('Select user')
			->setRequired();

		$roles = $this->rolesRepository->read('*')
			->where(RolesEntity::ColumnName, '!= ?', Conf::RoleGuest);

		if (!$this->user->isInRole(Conf::RoleAdmin)) {
			$roles->and(RolesEntity::ColumnName, '!= ?', Conf::RoleAdmin);
		}

		$roles = $roles->fetchPairs(RolesEntity::PrimaryKey, RolesEntity::ColumnName);
		$form->addMultiSelect(AccessRolesEntity::ColumnRoleId, 'Select roles', $roles)
			->setRequired();

		$form->addHidden(AccessRolesData::Id)
			->addRule($form::Integer)
			->setNullable();

		$form->addSubmit('send', 'Send');
		$form->onSuccess[] = $this->success(...);
		return $form;
	}


	/**
	 * @throws DriverException
	 */
	public function success(Form $form, AccessRolesData $data): void
	{
		try {
			$entity = new AccessRolesEntity;
			$entity->user_id = $data->user_id;

			$this->accessRolesRepository->getConnection()
				->begin();

			if ($data->id) {
				$this->accessRolesRepository
					->delete(AccessRolesEntity::ColumnUserId, $data->user_id)
					->execute();
			}

			foreach ($data->role_id as $item) {
				$entity->role_id = $item;
				$repository = $this->accessRolesRepository;
				$repository->getConnection()->insert($repository->getTableName(), $entity->toArray())
					->execute();
			}

			$this->accessRolesRepository->getConnection()->commit();
			$message = $data->id ? 'Roles have been updated.' : 'Role assigned.';
			$this->flashMessageOnPresenter($message, Alert::Success);

			if ($data->user_id) {
				$this->closeComponent();
			}

			$this->redrawSuccessFactory();
			$form->reset();

		} catch (Throwable $e) {
			$this->accessRolesRepository
				->getConnection()
				->rollback();

			$message = match ($e->getCode()) {
				1062 => 'The user already has this role assigned.',
				default => 'Unknown status code.',
			};

			$form->addError($message);
			$this->redrawControl($this->snippetFactory);
		}
	}


	/**
	 * @throws AbortException
	 * @throws AttributeDetectionException
	 * @throws Exception
	 * @throws BadRequestException
	 */
	#[Requires(ajax: true)]
	public function handleEdit(int $id): void
	{
		$items = $this->accessRolesRepository->getUserRoles($id);
		$items ?: $this->error();

		$userId = [];
		foreach ($items as $item) {
			$userId[AccessRolesEntity::ColumnUserId] = $item->user_id;
		}

		$roleId = [];
		foreach ($items as $item) {
			$roleId[$item->role_id] = $item->role_id;
		}

		$userId = $userId[AccessRolesEntity::ColumnUserId];
		$records = [
			AccessRolesEntity::ColumnUserId => $userId,
			AccessRolesEntity::ColumnRoleId => $roleId,
			AccessRolesData::Id => $userId,
		];

		$form = $this['factory'];
		$form->setDefaults($records);

		$buttonSend = $this->getFormComponent($form, 'send');
		$buttonSend->setCaption('Edit');

		$formUserId = $this->getFormComponent($form, 'user_id');
		$formUserId->setHtmlAttribute('data-locked');
		$this->offCanvasComponent();
	}


	/**
	 * @throws AbortException
	 * @throws AttributeDetectionException
	 * @throws BadRequestException
	 * @throws Exception
	 */
	#[Requires(ajax: true)]
	public function handleDelete(int $id): void
	{
		$items = $this->accessRolesRepository->find(AccessRolesEntity::ColumnUserId, $id)->record();
		$items ?: $this->error();
		$user = $this->accessRolesViewRepository
			->find(AccessRolesViewEntity::ColumnUserId, $items->user_id)
			->record();

		$this->deleteItems = $user->username;
		$this->modalComponent();
	}


	/**
	 * @throws AttributeDetectionException
	 * @throws DataGridException
	 */
	protected function createComponentGrid(string $name): DatagridComponent
	{
		$grid = new DatagridComponent($this, $name);
		$grid->setPrimaryKey('user_id');
		$grid->setDataSource($this->accessRolesViewRepository->getAllUsers());

		if ($this->translator) {
			$grid->setTranslator($this->translator);
		}

		if ($this->templateGrid) {
			$grid->setTemplateFile($this->templateGrid);
		}

		$grid->addColumnBase('username', 'Users');
		$grid->addColumnBase('role', 'Roles');
		$grid->addActionEdit('edit', 'Edit', 'edit!', ['id' => 'user_id']);
		$grid->addActionDeleteBase('delete', 'Delete', 'delete!', ['id' => 'user_id']);
		return $grid;
	}
}
