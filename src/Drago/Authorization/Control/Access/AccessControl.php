<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Access;

use Dibi\Exception;
use Drago\Application\UI\Alert;
use Drago\Attr\AttributeDetectionException;
use Drago\Authorization\Control\Base;
use Drago\Authorization\Control\Component;
use Drago\Authorization\Control\Factory;
use Drago\Authorization\Control\Roles\RolesRepository;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\BaseControl;
use Nette\SmartObject;
use Throwable;


/**
 * @property-read AccessTemplate $template
 */
class AccessControl extends Component implements Base
{
	use SmartObject;
	use Factory;

	public string $snippetFactory = 'access';
	public string $snippetItems = 'accessItems';


	public function __construct(
		private UsersRepository $usersRepository,
		private UsersRolesRepository $usersRolesRepository,
		private UsersRolesViewRepository $usersRolesViewRepository,
		private RolesRepository $rolesRepository,
	) {
	}


	public function render(): void
	{
		$template = $this->template;
		$template->setFile($this->templateFactory ?: __DIR__ . '/Access.latte');
		$template->setTranslator($this->translator);
		$template->form = $this['factory'];
		$template->render();
	}


	/**
	 * @throws Exception
	 * @throws AttributeDetectionException
	 */
	public function renderItems(): void
	{
		$users = $this->usersRolesViewRepository->getAllUsersRoles();
		$usersRoleList = [];
		foreach ($users as $user) {
			$usersRoleList[$user->user_id] = $user;
		}

		foreach ($usersRoleList as $user) {
			$roleList = [];
			foreach ($users as $role) {
				if ($user->user_id === $role->user_id) {
					$roleList[] = $role->role;
				}
			}
			$user->role = $roleList;
		}

		$template = $this->template;
		$template->setFile($this->templateItems ?: __DIR__ . '/AccessItems.latte');
		$template->setTranslator($this->translator);
		$template->deleteId = $this->deleteId;
		$template->usersRoles = $usersRoleList;
		$template->render();
	}


	/**
	 * @throws Exception
	 * @throws BadRequestException
	 * @throws AttributeDetectionException
	 */
	public function handleEdit(int $id): void
	{
		$access = $this->usersRolesRepository->getUserRoles($id);
		$access ?: $this->error();

		$userId = [];
		foreach ($access as $arr) {
			$userId[UsersRolesData::USER_ID] = $arr->user_id;
		}

		$roleId = [];
		foreach ($access as $arr) {
			$roleId[$arr->role_id] = $arr->role_id;
		}

		$userId = $userId[UsersRolesData::USER_ID];
		$items = [
			UsersRolesData::USER_ID => $userId,
			UsersRolesData::EDIT_ID => $userId,
			UsersRolesData::ROLE_ID => $roleId,
		];

		if ($this->getSignal()) {
			$form = $this['factory'];
			if ($form instanceof Form) {
				$form->setDefaults($items);
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
		$access = $this->usersRolesRepository->getRecord($id);
		$access ?: $this->error();
		$this->deleteId = $access->user_id;
		if ($this->isAjax()) {
			$this->getPresenter()
				->redrawControl($this->snippetItems);
		}
	}


	/**
	 * @throws BadRequestException
	 * @throws Exception
	 * @throws AttributeDetectionException
	 */
	public function handleDeleteConfirm(int $confirm, int $id): void
	{
		$access = $this->usersRolesRepository->getRecord($id);
		$access ?: $this->error();

		if ($confirm === 1) {
			$records = $this->usersRolesRepository->getUserRoles($id);
			$entity = new UsersRolesEntity;
			foreach ($records as $record) {
				$entity->user_id = $record->user_id;
				$entity->role_id = $record->role_id;
				$this->usersRolesRepository->delete($entity);
			}

			$this->getPresenter()->flashMessage(
				'Role removed.',
				Alert::DANGER,
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
		$form = $this->create();

		$users = $this->usersRepository->getAllUsers();
		$form->addSelect(UsersRolesData::USER_ID, 'User', $users)
			->setPrompt('Select user')
			->setRequired();

		$roles = $this->rolesRepository->getRoles();
		$form->addMultiSelect(UsersRolesData::ROLE_ID, 'Select roles', $roles)
			->setRequired();

		$form->addHidden(UsersRolesData::EDIT_ID)
			->addRule($form::INTEGER)
			->setNullable();

		$form->addSubmit('send', 'Send');
		$form->onSuccess[] = [$this, 'success'];
		return $form;
	}


	public function success(Form $form, UsersRolesData $data): void
	{
		try {
			if (!$data->edit_id) {
				$entity = new UsersRolesEntity;
				$entity->user_id = $data->user_id;
				foreach ($data->role_id as $item) {
					$entity->role_id = $item;
					$this->usersRolesRepository->insert($entity);
				}
			} else {
				$allUserRoles = $this->usersRolesRepository->getAllUserRoles();
				$roleList = [];
				foreach ($allUserRoles as $arr) {
					if ($arr->user_id === $data->edit_id) {
						$roleList[] = $arr->role_id;
					}
				}
				$insertRoles = array_diff($data->role_id, $roleList);
				$deleteRoles = array_diff($roleList, $data->role_id);
				if (count($insertRoles)) {
					$entity = new UsersRolesEntity;
					$entity->user_id = $data->user_id;
					foreach ($insertRoles as $role) {
						$entity->role_id = $role;
						$this->usersRolesRepository->insert($entity);
					}
				}

				if (count($deleteRoles)) {
					$findRoles = $this->usersRolesRepository->getUserRoles($data->edit_id);
					$entity = new UsersRolesEntity;
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

			$message = $data->edit_id ? 'Roles have been updated.' : 'Role assigned.';
			$this->getPresenter()->flashMessage($message, Alert::INFO);

			if ($this->isAjax()) {
				if ($data->user_id) {
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

		} catch (Throwable $e) {
			$message = match ($e->getCode()) {
				1062 => 'The user already has this role assigned.',
				default => 'Unknown status code.',
			};

			$form->addError($message);
			if ($this->isAjax()) {
				$this->getPresenter()->redrawControl($this->snippetFactory);
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
