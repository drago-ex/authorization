<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Access;

use App\Authorization\Control\ComponentTemplate;
use Contributte\Datagrid\Column\Action\Confirmation\StringConfirmation;
use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Exception\DatagridException;
use Dibi\DriverException;
use Dibi\Exception;
use Drago\Application\UI\Alert;
use Drago\Attr\AttributeDetectionException;
use Drago\Authorization\Control\Base;
use Drago\Authorization\Control\Component;
use Drago\Authorization\Control\Factory;
use Drago\Authorization\Control\Roles\RolesRepository;
use Drago\Authorization\FluentWithClassDataSource;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Throwable;


/**
 * @property-read ComponentTemplate $template
 */
class AccessControl extends Component implements Base
{
	use Factory;

	public string $snippetFactory = 'access';


	public function __construct(
		private readonly UsersRepository $usersRepository,
		private readonly UsersRolesRepository $usersRolesRepository,
		private readonly UsersRolesViewRepository $usersRolesViewRepository,
		private readonly RolesRepository $rolesRepository,
		private readonly DepartmentsRepository $departmentsRepository,
		private readonly UsersDepartmentsRepository $usersDepartmentsRepository,
	) {
	}


	public function render(): void
	{
		$template = $this->template;
		$template->setFile($this->templateControl ?: __DIR__ . '/Access.latte');
		$template->setTranslator($this->translator);
		$template->uniqueComponentId = $this->getUniqueComponent($this->openComponentType);
		$template->render();
	}


	public function getUniqueComponent(string $type): string
	{
		return $this->getUniqueIdComponent($type);
	}


	public function handleClickOpen(): void
	{
		if ($this->isAjax()) {
			$component = $this->getUniqueComponent($this->openComponentType);
			$this->getPresenter()->payload->{$this->openComponentType} = $component;
			$this->redrawControl($this->snippetFactory);
		}
	}


	/**
	 * @throws AttributeDetectionException|Exception
	 */
	protected function createComponentFactory(): Form
	{
		$form = $this->create();
		$users = $this->usersRepository->getAllUsers();

		if ($this->getSignal()) {
			$id = (int) $this->getParameter('id');
			$user = $this->usersRepository->getUserById($id);
		}

		$form->addSelect(UsersRolesData::USER_ID, 'User', $user ?? $users)
			->setPrompt('Select user')
			->setRequired();

		$role = $this->rolesRepository->getRolesAll();
		$roles = [];
		foreach ($role as $item) {
			$roles[$item->id] = $item->name;
			if ($item->description) {
				$roles[$item->id] = $item->description;
			}
		}

		$form->addMultiSelect(UsersRolesData::ROLE_ID, 'Select roles', $roles)
			->setRequired();


		$departments = $this->departmentsRepository->getAll();
		$form->addMultiSelect(UsersRolesData::DEPARTMENT_ID, 'Department', $departments);

		$form->addHidden(UsersRolesData::ID)
			->addRule($form::Integer)
			->setNullable();

		$form->addSubmit('send', 'Send');
		$form->onSuccess[] = [$this, 'success'];
		return $form;
	}


	/**
	 * @throws AbortException|DriverException
	 */
	public function success(Form $form, UsersRolesData $data): void
	{
		try {
			$entity = new UsersRolesEntity;
			$userDepartmentEntity = new UsersDepartmentsEntity;

			$entity->user_id = $data->user_id;
			$userDepartmentEntity->user_id = $data->user_id;

			$this->usersRolesRepository->getDb()->begin();

			if ($data->id) {
				$this->usersRolesRepository->deleteByUserId($data->user_id);
				$this->usersDepartmentsRepository->deleteByUserId($data->user_id);
			}

			foreach ($data->role_id as $item) {
				$entity->role_id = $item;
				$this->usersRolesRepository->insert($entity);
			}


			foreach ($data->department_id as $item) {
				$userDepartmentEntity->department_id = $item;
				$this->usersDepartmentsRepository->save($userDepartmentEntity);
			}

			$this->usersRolesRepository->getDb()->commit();

			$message = $data->id ? 'Roles have been updated.' : 'Role assigned.';
			$this->getPresenter()->flashMessage($message, Alert::Info);

			if ($this->isAjax()) {
				if ($data->user_id) {
					$this->getPresenter()->payload->close = 'close';
				}
				$this->getPresenter()->redrawControl($this->snippetMessage);
				$this->redrawControl($this->snippetFactory);
				$this['grid']->reload();
				$form->reset();

			} else {
				$this->redirect('this');
			}

		} catch (Throwable $e) {
			$this->usersRolesRepository->getDb()->rollback();
			$message = match ($e->getCode()) {
				1 => 'The user already has this role assigned.',
				default => 'Unknown status code.',
			};

			$form->addError($message);
			$this->isAjax()
				? $this->redrawControl($this->snippetFactory)
				: $this->redirect('this');
		}
	}


	/**
	 * @throws AbortException
	 * @throws AttributeDetectionException
	 * @throws Exception
	 * @throws BadRequestException
	 */
	public function handleEdit(int $id): void
	{
		$items = $this->usersRolesRepository->getUserRoles($id);
		$items ?: $this->error();

		$userId = [];
		foreach ($items as $item) {
			$userId[UsersRolesData::USER_ID] = $item->user_id;
		}

		$roleId = [];
		foreach ($items as $item) {
			$roleId[$item->role_id] = $item->role_id;
		}

		$userDepartmentId = $this->usersDepartmentsRepository->findByUserId($id);

		$userDepartments = [];
		foreach ($userDepartmentId as $item) {
			$userDepartments[] = $item->department_id;
		}

		$userId = $userId[UsersRolesData::USER_ID];
		$records = [
			UsersRolesData::USER_ID => $userId,
			UsersRolesData::ROLE_ID => $roleId,
			UsersRolesData::ID => $userId,
			UsersRolesData::DEPARTMENT_ID => $userDepartments,
		];

		$form = $this['factory'];
		$form->setDefaults($records);

		$buttonSend = $this->getFormComponent($form, 'send');
		$buttonSend->setCaption('Edit');

		$formUserId = $this->getFormComponent($form, 'user_id');
		$formUserId->setHtmlAttribute('data-locked');

		if ($this->isAjax()) {
			$component = $this->getUniqueComponent($this->openComponentType);
			$this->getPresenter()->payload->{$this->openComponentType} = $component;
			$this->redrawControl($this->snippetFactory);

		} else {
			$this->redirect('this');
		}
	}


	/**
	 * @throws AbortException
	 * @throws AttributeDetectionException
	 * @throws BadRequestException
	 * @throws Exception
	 */
	public function handleDelete(int $id): void
	{
		$items = $this->usersRolesRepository->getRecord($id);
		$items ?: $this->error();

		$records = $this->usersRolesRepository->getUserRoles($items->user_id);
		$entity = new UsersRolesEntity;
		foreach ($records as $record) {
			$entity->user_id = $record->user_id;
			$entity->role_id = $record->role_id;
			$this->usersRolesRepository->deleteRole($entity);
		}

		$this->getPresenter()->flashMessage(
			'Role removed.',
			Alert::Danger,
		);

		if ($this->isAjax()) {
			$this->getPresenter()->redrawControl($this->snippetMessage);
			$this['grid']->reload();

		} else {
			$this->redirect('this');
		}
	}


	/**
	 * @throws AttributeDetectionException
	 * @throws DataGridException
	 */
	protected function createComponentGrid($name): DataGrid
	{
		$grid = new DataGrid($this, $name);
		$data = new FluentWithClassDataSource($this->usersRolesViewRepository->getAllUsers(), 'USER_ID', UsersRolesViewEntity::class);
		$grid->setPrimaryKey('user_id');
		$grid->setDataSource($data);
		$grid->setAutoSubmit(false);
		$grid->setStrictSessionFilterValues(false);

		if ($this->translator) {
			$grid->setTranslator($this->translator);
		}

		if ($this->templateGrid) {
			$grid->setTemplateFile($this->templateGrid);
		}

		$grid->addColumnText('username', 'Users')
			->setFilterText();

		$grid->addColumnText('role', 'Roles')
			->setFilterText();

		$grid->addAction('edit', 'Edit', 'edit!', ['id' => 'user_id'])
			->setClass('btn btn-xs btn-primary text-white ajax');

		$confirm = 'Are you sure you want to delete the selected item?';
		if ($this->translator) {
			$confirm = $this->translator->translate($confirm);
		}
		$grid->addAction('delete', 'Delete', 'delete!', ['id' => 'user_id'])
			->setClass('btn btn-xs btn-danger ajax')
			->setConfirmation(new StringConfirmation($confirm));

		return $grid;
	}
}
