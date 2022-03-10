<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Access;

use Dibi\Exception;
use Drago\Application\UI\Alert;
use Drago\Authorization\Control\Base;
use Drago\Authorization\Control\Component;
use Drago\Authorization\Service\Data\UsersRolesData;
use Drago\Authorization\Service\Entity\UsersRolesEntity;
use Drago\Authorization\Service\Repository\RolesRepository;
use Drago\Authorization\Service\Repository\UsersRepository;
use Drago\Authorization\Service\Repository\UsersRolesRepository;
use Drago\Authorization\Service\Repository\UsersRolesViewRepository;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\BaseControl;


class AccessControl extends Component implements Base
{
	public string $snippetFactory = 'access';
	public string $snippetRecords = 'accessRecords';


	public function __construct(
		private UsersRepository $usersRepository,
		private UsersRolesRepository $usersRolesRepository,
		private UsersRolesViewRepository $usersRolesViewRepository,
		private RolesRepository $rolesRepository,
	) {
	}


	public function render(): void
	{
		$template = __DIR__ . '/Templates/Access.add.latte';
		$template = $this->templateAdd ?: $template;
		$items = [
			'form' => $this['factory'],
		];
		$this->setRenderControl($template, $items);
	}


	/**
	 * @throws Exception
	 */
	public function renderRecords(): void
	{
		$template = __DIR__ . '/Templates/Access.records.latte';
		$template = $this->templateRecords ?: $template;
		$users = $this->usersRolesViewRepository->getAllUsersRoles();

		$usersRoleList = [];
		foreach ($users as $user) {
			$usersRoleList[$user->user_id] = $user;
		}

		foreach ($usersRoleList as $user) {
			$usersRoleList[$user->user_id] = $user;
			$roleList = [];
			foreach ($users as $role) {
				if ($user->user_id === $role->user_id) {
					$roleList[] = $role->role;
				}
			}

			$user->role = $roleList;
			$usersRoleList[$user->user_id] = $user;
		}

		$items = [
			'usersRoles' => $usersRoleList,
			'deleteId' => $this->deleteId,
		];

		$this->setRenderControl($template, $items);
	}


	/**
	 * @throws Exception
	 * @throws BadRequestException
	 */
	public function handleEdit(int $id): void
	{
		$access = $this->usersRolesRepository->getUserRoles($id);
		$access ?: $this->error();

		$userId = [];
		foreach ($access as $arr) {
			$userId[UsersRolesData::USER_ID] = $arr->user_id;
		}

		$rolesId = [];
		foreach ($access as $arr) {
			$rolesId[$arr->role_id] = $arr->role_id;
		}

		$userId = $userId[UsersRolesData::USER_ID];
		$items = [
			UsersRolesData::USER_ID => $userId,
			UsersRolesData::EDIT_ID => $userId,
			UsersRolesEntity::ROLE_ID => $rolesId,
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
				$this->presenter->payload->access = 'access';
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
		$access = $this->usersRolesRepository->getRecord($id);
		$access ?: $this->error();
		$this->deleteId = $access->user_id;

		if ($this->isAjax()) {
			$this->redrawPresenter($this->snippetRecords);
		}
	}


	/**
	 * @throws BadRequestException
	 * @throws Exception
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

			$this->flashMessagePresenter('Access removed.', Alert::DANGER);
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

		$users = $this->usersRepository->getAllUsers();
		$form->addSelect(UsersRolesData::USER_ID, 'User', $users)
			->setPrompt('Select user')
			->setRequired();

		$roles = $this->rolesRepository->getRoles();
		$form->addMultiSelect(UsersRolesData::ROLE_ID, 'Select roles', $roles)
			->setRequired();

		$form->addHidden(UsersRolesData::EDIT_ID, 0)
			->addRule(Form::INTEGER);

		$form->addSubmit('send', 'Send')
			->setHtmlAttribute('onclick', 'if( Nette.validateForm(this.form) ) { this.disabled=true; } return false;');

		$form->onSuccess[] = [$this, 'success'];
		return $form;
	}


	public function success(Form $form, UsersRolesData $data): void
	{
		try {
			$form->reset();
			$formId = $form[UsersRolesData::EDIT_ID];
			if ($formId instanceof BaseControl) {
				$formId->setDefaultValue(0)
					->addRule(Form::INTEGER);
			}

			if (!$data->edit_id) {
				$entity = new UsersRolesEntity;
				$entity->user_id = $data->user_id;

				foreach ($data->role_id as $item) {
					$entity->role_id = $item;
					$this->usersRolesRepository->save($entity);
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
						$this->usersRolesRepository->save($entity);
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

			$message = $data->edit_id ? 'Access was updated.' : 'Access added.';
			$this->flashMessagePresenter($message);

		} catch (\Throwable $e) {
			$message = match ($e->getCode()) {
				1062 => 'The user already has this role assigned.',
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
