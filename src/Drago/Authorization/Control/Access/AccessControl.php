<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Access;

use App\Authorization\Control\ComponentTemplate;
use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Exception\DatagridException;
use Dibi\DriverException;
use Dibi\Exception;
use Drago\Application\UI\Alert;
use Drago\Attr\AttributeDetectionException;
use Drago\Authorization\Conf;
use Drago\Authorization\Control\Base;
use Drago\Authorization\Control\Component;
use Drago\Authorization\Control\Factory;
use Drago\Authorization\Control\Roles\RolesEntity;
use Drago\Authorization\Control\Roles\RolesRepository;
use Drago\Authorization\Datagrid\DatagridComponent;
use Nette\Application\AbortException;
use Nette\Application\Attributes\Parameter;
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

	#[Parameter]
	private int $id;

	public string $snippetFactory = 'access';


	public function __construct(
		private readonly AccessRepository $usersRepository,
		private readonly AccessRolesRepository $usersRolesRepository,
		private readonly AccessRolesViewRepository $usersRolesViewRepository,
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


	/**
	 * @throws AttributeDetectionException
	 */
	protected function createComponentFactory(): Form
	{
		$form = $this->create();
		$users = $this->usersRepository->getAllUsers();

		if ($this->getSignal()) {
			$user = $this->usersRepository->getUserById($this->id);
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
		$form->onSuccess[] = [$this, 'success'];
		return $form;
	}


	/**
	 * @throws DriverException
	 */
	#[Requires(ajax: true)]
	public function success(Form $form, AccessRolesData $data): void
	{
		try {
			$entity = new AccessRolesEntity;
			$entity->user_id = $data->user_id;

			$this->usersRolesRepository->getConnection()
				->begin();

			if ($data->id) {
				$this->usersRolesRepository
					->delete(AccessRolesEntity::ColumnUserId, $data->user_id)
					->execute();
			}

			foreach ($data->role_id as $item) {
				$entity->role_id = $item;
				$this->usersRolesRepository->save($entity);
			}

			$this->usersRolesRepository->getConnection()->commit();
			$message = $data->id ? 'Roles have been updated.' : 'Role assigned.';
			$this->getPresenter()->flashMessage($message, Alert::Info);

			if ($data->user_id) {
				$this->getPresenter()->payload->close = 'close';
			}

			$this->redrawControlMessage();
			$this->redrawControl($this->snippetFactory);
			$this['grid']->reload();
			$form->reset();

		} catch (Throwable $e) {
			$this->usersRolesRepository
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
		$items = $this->usersRolesRepository->getUserRoles($id);
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
		$items = $this->usersRolesRepository->get($id)->record();
		$items ?: $this->error();

		$records = $this->usersRolesRepository->getUserRoles($items->user_id);
		foreach ($records as $record) {
			$this->usersRolesRepository->delete(AccessRolesEntity::ColumnRoleId, $record->role_id)
				->and(AccessRolesEntity::ColumnUserId, '= ?', $record->user_id)
				->execute();
		}

		$this->getPresenter()->flashMessage('Role removed.', Alert::Danger);
		$this->redrawControlMessage();
		$this['grid']->reload();
	}


	/**
	 * @throws AttributeDetectionException
	 * @throws DataGridException
	 */
	protected function createComponentGrid($name): DataGrid
	{
		$grid = new DatagridComponent($this, $name);
		$grid->setPrimaryKey('user_id');
		$grid->setDataSource($this->usersRolesViewRepository->getAllUsers());
		$grid->init();

		if ($this->templateGrid) {
			$grid->setTemplateFile($this->templateGrid);
		}

		$grid->addColumnBase('username', 'Users');
		$grid->addColumnBase('role', 'Roles');
		$grid->addActionEdit('edit', 'Edit', 'edit!', ['id' => 'user_id']);
		$grid->addActionDelete('delete', 'Delete', 'delete!', ['id' => 'user_id']);
		return $grid;
	}
}
