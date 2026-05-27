<?php

declare(strict_types=1);

namespace Drago\Authorization\Control\Access;

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
use Nette\Application\Attributes\Requires;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\Security\User;
use Nette\SmartObject;
use Throwable;


/** Manages user role assignments and access control. */
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


	/** Renders the access control template. */
	public function render(): void
	{
		$template = $this->createRender();
		$template->setFile($this->templateControl ?: __DIR__ . '/Access.latte');
		$template->render();
	}


	/** Opens the component offcanvas. */
	#[Requires(ajax: true)]
	public function handleClickOpenComponent(): void
	{
		$this->offCanvasComponent();
	}


	/** Creates the delete form for user access. */
	protected function createComponentDelete(): Form
	{
		$form = $this->createDelete($this->id);
		$form->addSubmit('confirm', 'Confirm')
			->onClick[] = function (SubmitButton $button): void {
				$form = $button->getForm();
				if ($form instanceof Form) {
					$id = (int) $form->getValues()['id'];
					$this->delete($form, $id);
				}
			};
		return $form;
	}


	/** Deletes user access. */
	public function delete(Form $form, int $id): void
	{
		try {
			$this->accessRolesRepository
				->delete(AccessRolesEntity::ColumnUserId, $id)
				->execute();

			$this->flashMessageOnPresenter('Access deleted.');
			$this->closeComponent();
			$this->redrawDeleteFactoryAll();

		} catch (Throwable $e) {
			$this->flashMessageOnPresenter('Unknown status code.', Alert::Warning);
			$this->redrawMessageOnPresenter();
		}
	}


	/** Creates the form for assigning roles to a user.
	 * @throws AttributeDetectionException
	 */
	protected function createComponentFactory(): Form
	{
		$form = $this->create();
		$users = $this->accessRepository->getAllUsers();

		$user = null;
		if ($this->getSignal()) {
			$user = $this->accessRepository->getUserById($this->id);
		}

		/** @var array<int, string> $items */
		$items = is_array($user) ? $user : $users;

		$form->addSelect(AccessRolesEntity::ColumnUserId, 'User', $items)
			->setPrompt('Select user')
			->setRequired();

		$roles = $this->rolesRepository->read('*')
			->where(RolesEntity::ColumnName, '!= ?', Conf::RoleAdmin);

		if (!$this->user->isInRole(Conf::RoleAdmin)) {
			$roles->and(RolesEntity::ColumnName, '!= ?', Conf::RoleAdmin);
		}

		$roles = $roles->fetchPairs(RolesEntity::PrimaryKey, RolesEntity::ColumnName);
		$form->addMultiSelect(AccessRolesEntity::ColumnRoleId, 'Select roles', $roles)
			->setRequired();

		$form->addHidden(AccessRolesValues::Id)
			->addRule($form::Integer)
			->setHtmlAttribute('data-locked')
			->setNullable();

		$form->addSubmit('send', 'Send');
		$form->onSuccess[] = function (Form $form, AccessRolesValues $data): void {
			$this->success($form, $data);
		};
		return $form;
	}


	/** @throws DriverException */
	private function success(Form $form, AccessRolesValues $data): void
	{
		try {
			$entity = new AccessRolesEntity;
			if (isset($data->user_id)) {
				$entity->user_id = (int) $data->user_id;
			}

			$this->accessRolesRepository
				->getConnection()
				->begin();

			if (isset($data->id)) {
				$this->accessRolesRepository
					->delete(AccessRolesEntity::ColumnUserId, (int) $data->user_id)
					->execute();
			}

			if (isset($data->role_id)) {
				foreach ($data->role_id as $item) {
					$entity->role_id = (int) $item;
					$repository = $this->accessRolesRepository;
					$repository->getConnection()
						->insert($repository->getTableName(), $entity->toArray())
						->execute();
				}
			}

			$this->accessRolesRepository->getConnection()->commit();
			$this->flashMessageOnPresenter(isset($data->id) ? 'Roles updated.' : 'Role assigned.', Alert::Success);

			if (isset($data->user_id)) {
				$this->closeComponent();
			}

			$this->redrawSuccessFactory();
			$form->reset();

		} catch (Throwable $e) {
			$this->accessRolesRepository
				->getConnection()
				->rollback();

			$form->addError($e->getCode() === 1062 ? 'Role already assigned.' : 'Unknown error.');
			$this->redrawControl($this->snippetFactory);
		}
	}


	/**
	 * Edits user roles by ID.
	 * @throws AttributeDetectionException
	 * @throws Exception
	 */
	#[Requires(ajax: true)]
	public function handleEdit(int $id): void
	{
		$items = $this->accessRolesRepository->getUserRoles($id);
		if ($items !== []) {
			$userId = $items[0]->user_id ?? null;
			$roleId = array_column($items, 'role_id');

			$form = $this['factory'];
			$form->setDefaults([
				AccessRolesEntity::ColumnUserId => $userId,
				AccessRolesEntity::ColumnRoleId => $roleId,
				AccessRolesValues::Id => $userId,
			]);

			$this->getFormComponent($form, 'send')?->setCaption('Edit');
			$this->getFormComponent($form, 'user_id')?->setHtmlAttribute('data-locked');
			$this->offCanvasComponent();
		}
	}


	/** Deletes user roles by ID.
	 * @throws AttributeDetectionException
	 * @throws Exception
	 */
	#[Requires(ajax: true)]
	public function handleDelete(int $id): void
	{
		$items = $this->accessRolesRepository->find(AccessRolesEntity::ColumnUserId, $id)->record();
		if ($items !== null) {
			$user = $this->accessRolesViewRepository
				->find(AccessRolesViewEntity::ColumnUserId, $items->user_id)
				->record();

			if ($user !== null) {
				$this->deleteItems = $user->username;
				$this->modalComponent();
			}
		}
	}


	/**
	 * Creates the user roles data grid.
	 * @throws AttributeDetectionException
	 * @throws DatagridException
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
