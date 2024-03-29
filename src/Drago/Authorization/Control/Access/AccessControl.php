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
use Dibi\Exception;
use Drago\Application\UI\Alert;
use Drago\Attr\AttributeDetectionException;
use Drago\Authorization\Conf;
use Drago\Authorization\Control\Base;
use Drago\Authorization\Control\Component;
use Drago\Authorization\Control\Factory;
use Drago\Authorization\Control\Roles\RolesEntity;
use Drago\Authorization\Control\Roles\RolesRepository;
use Nette\Application\AbortException;
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
		private readonly AccessRepository $usersRepository,
		private readonly AccessRolesRepository $usersRolesRepository,
		private readonly AccessRolesViewRepository $usersRolesViewRepository,
		private readonly RolesRepository $rolesRepository,
		private readonly User $user,
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


	public function handleClickOpenComponent(): void
	{
		if ($this->isAjax()) {
			$component = $this->getUniqueComponent($this->openComponentType);
			$this->getPresenter()->payload->{$this->openComponentType} = $component;
			$this->redrawControl($this->snippetFactory);
		}
	}


	/**
	 * @throws AttributeDetectionException
	 */
	protected function createComponentFactory(): Form
	{
		$form = $this->create();
		$users = $this->usersRepository->getAllUsers();

		if ($this->getSignal()) {
			$id = (int) $this->getParameter('id');
			$user = $this->usersRepository->getUserById($id);
		}

		$form->addSelect(AccessRolesEntity::UserId, 'User', $user ?? $users)
			->setPrompt('Select user')
			->setRequired();

		$roles = $this->rolesRepository->all()
			->where(RolesEntity::Name, '!= ?', Conf::RoleGuest);

		if (!$this->user->isInRole(Conf::RoleAdmin)) {
			$roles->and(RolesEntity::Name, '!= ?', Conf::RoleAdmin);
		}

		$roles = $roles->fetchPairs(RolesEntity::Id, RolesEntity::Name);
		$form->addMultiSelect(AccessRolesEntity::RoleId, 'Select roles', $roles)
			->setRequired();

		$form->addHidden(AccessRolesData::Id)
			->addRule($form::INTEGER)
			->setNullable();

		$form->addSubmit('send', 'Send');
		$form->onSuccess[] = [$this, 'success'];
		return $form;
	}


	/**
	 * @throws AbortException
	 */
	public function success(Form $form, AccessRolesData $data): void
	{
		try {
			if (!$data->id) {
				$entity = new AccessRolesEntity;
				$entity->user_id = $data->user_id;
				foreach ($data->role_id as $item) {
					$entity->role_id = $item;
					$this->usersRolesRepository->insert($entity);
				}
			} else {
				$allUserRoles = $this->usersRolesRepository->getAllUserRoles();
				$roleList = [];
				foreach ($allUserRoles as $arr) {
					if ($arr->user_id === $data->id) {
						$roleList[] = $arr->role_id;
					}
				}
				$insertRoles = array_diff($data->role_id, $roleList);
				$deleteRoles = array_diff($roleList, $data->role_id);
				if (count($insertRoles)) {
					$entity = new AccessRolesEntity;
					$entity->user_id = $data->user_id;
					foreach ($insertRoles as $role) {
						$entity->role_id = $role;
						$this->usersRolesRepository->insert($entity);
					}
				}

				if (count($deleteRoles)) {
					$findRoles = $this->usersRolesRepository->getUserRoles($data->id);
					$entity = new AccessRolesEntity;
					foreach ($deleteRoles as $roleForDelete) {
						foreach ($findRoles as $arr) {
							if ($arr->role_id === $roleForDelete) {
								$entity->user_id = $arr->user_id;
								$entity->role_id = $arr->role_id;
								$this->usersRolesRepository->delete($entity);
							}
						}
					}
				}
			}

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
			$message = match ($e->getCode()) {
				1062 => 'The user already has this role assigned.',
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
			$userId[AccessRolesEntity::UserId] = $item->user_id;
		}

		$roleId = [];
		foreach ($items as $item) {
			$roleId[$item->role_id] = $item->role_id;
		}

		$userId = $userId[AccessRolesEntity::UserId];
		$records = [
			AccessRolesEntity::UserId => $userId,
			AccessRolesEntity::RoleId => $roleId,
			AccessRolesData::Id => $userId,
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
		$entity = new AccessRolesEntity;
		foreach ($records as $record) {
			$entity->user_id = $record->user_id;
			$entity->role_id = $record->role_id;
			$this->usersRolesRepository->delete($entity);
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
		$grid->setPrimaryKey('user_id');
		$grid->setDataSource($this->usersRolesViewRepository->getAllUsers());

		if ($this->translator) {
			$grid->setTranslator($this->translator);
		}

		if ($this->templateGrid) {
			$grid->setTemplateFile($this->templateGrid);
		}

		$grid->addColumnText('username', 'Users')
			->setSortable()
			->setFilterText();

		$grid->addColumnText('role', 'Roles')
			->setSortable()
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
