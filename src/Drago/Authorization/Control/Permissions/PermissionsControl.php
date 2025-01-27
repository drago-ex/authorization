<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Permissions;

use App\Authorization\Control\ComponentTemplate;
use Contributte\Datagrid\Exception\DatagridColumnStatusException;
use Contributte\Datagrid\Exception\DatagridException;
use Dibi\Exception;
use Drago\Application\UI\Alert;
use Drago\Attr\AttributeDetectionException;
use Drago\Authorization\Conf;
use Drago\Authorization\Control\Base;
use Drago\Authorization\Control\Component;
use Drago\Authorization\Control\DatagridComponent;
use Drago\Authorization\Control\Factory;
use Drago\Authorization\Control\Privileges\PrivilegesEntity;
use Drago\Authorization\Control\Privileges\PrivilegesRepository;
use Drago\Authorization\Control\Resources\ResourcesEntity;
use Drago\Authorization\Control\Resources\ResourcesRepository;
use Drago\Authorization\Control\Roles\RolesEntity;
use Drago\Authorization\Control\Roles\RolesRepository;
use Nette\Application\Attributes\Requires;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\SmartObject;
use Throwable;


/**
 * Permissions control to manage roles and permissions
 * @property-read ComponentTemplate $template
 */
class PermissionsControl extends Component implements Base
{
	use SmartObject;
	use Factory;

	/** @var string Snippet factory identifier for rendering permissions */
	public string $snippetFactory = 'permissions';


	/**
	 * Constructor for initializing repository dependencies and cache.
	 */
	public function __construct(
		private readonly Cache $cache,
		private readonly RolesRepository $rolesRepository,
		private readonly ResourcesRepository $resourcesRepository,
		private readonly PrivilegesRepository $privilegesRepository,
		private readonly PermissionsRepository $permissionsRepository,
		private readonly PermissionsViewRepository $permissionsViewRepository,
		private readonly PermissionsRolesViewRepository $permissionsRolesViewRepository,
	) {
	}


	/**
	 * Renders the permissions control.
	 */
	public function render(): void
	{
		$template = $this->createRender();
		$template->setFile($this->templateControl ?: __DIR__ . '/Permissions.latte');
		$template->render();
	}


	/**
	 * Handles the click event to open the component via AJAX.
	 */
	#[Requires(ajax: true)]
	public function handleClickOpenComponent(): void
	{
		$this->offCanvasComponent();
	}


	/**
	 * Creates and returns the delete form.
	 * The form is used to confirm and execute deletion of permissions.
	 */
	protected function createComponentDelete(): Form
	{
		$form = $this->createDelete($this->id);
		$form->addSubmit('confirm', 'Confirm')
			->onClick[] = $this->delete(...);
		return $form;
	}


	/**
	 * Deletes a permission and updates the cache.
	 * Displays success or failure messages based on the operation result.
	 */
	public function delete(Form $form, \stdClass $data): void
	{
		try {
			$this->permissionsRepository
				->delete(PermissionsEntity::PrimaryKey, $data->id)
				->execute();

			$this->cache->remove(Conf::Cache);
			$this->flashMessageOnPresenter('Permissions deleted.');
			$this->closeComponent();
			$this->redrawDeleteFactoryAll();

		} catch (Throwable $e) {
			$message = 'Unknown status code.';
			$this->flashMessageOnPresenter($message, Alert::Warning);
			$this->redrawMessageOnPresenter();
		}
	}


	/**
	 * Creates the form for adding/editing permissions.
	 * The form includes role, resource, privilege, and permission selections.
	 */
	protected function createComponentFactory(): Form
	{
		$form = $this->create();
		$roles = $this->rolesRepository->read('*')
			->where(RolesEntity::ColumnName, '!= ?', Conf::RoleAdmin)
			->fetchPairs(RolesEntity::PrimaryKey, RolesEntity::ColumnName);

		$form->addSelect(PermissionsEntity::ColumnRoleId, 'Role', $roles)
			->setPrompt('Select role')
			->setRequired();

		$resources = $this->resourcesRepository->read('*')
			->fetchPairs(ResourcesEntity::PrimaryKey, ResourcesEntity::ColumnName);

		$form->addSelect(PermissionsEntity::ColumnResourceId, 'Resource', $resources)
			->setPrompt('Select resource')
			->setRequired();

		$privileges = $this->privilegesRepository->read('*')
			->fetchPairs(PrivilegesEntity::PrimaryKey, PrivilegesEntity::ColumnName);

		$form->addSelect(PermissionsEntity::ColumnPrivilegeId, 'Actions and signals', $privileges)
			->setPrompt('Select privilege')
			->setRequired();

		$permission = [
			'Deny',
			'Allow',
		];

		$form->addSelect(PermissionsEntity::ColumnAllowed, 'Permission', $permission)
			->setPrompt('Select permission')
			->setRequired();

		$form->addHidden(PermissionsEntity::PrimaryKey)
			->addRule($form::Integer)
			->setNullable();

		$form->addSubmit('send', 'Send');
		$form->onSuccess[] = $this->success(...);
		return $form;
	}


	/**
	 * Success handler after submitting the permissions form.
	 * Saves the permission and provides feedback messages.
	 */
	private function success(Form $form, PermissionsData $data): void
	{
		try {
			$this->permissionsRepository->save($data->toArray());
			$this->cache->remove(Conf::Cache);

			$message = $data->id ? 'Permission was updated.' : 'Permission added.';
			$this->flashMessageOnPresenter($message, Alert::Success);

			if ($data->id) {
				$this->closeComponent();
			}

			$this->redrawSuccessFactory();
			$form->reset();

		} catch (Throwable $e) {
			$message = match ($e->getCode()) {
				1062 => 'This permission is already granted.',
				default => 'Unknown status code.',
			};

			$form->addError($message);
			$this->redrawControl($this->snippetFactory);
		}
	}


	/**
	 * Handles the edit action for a permission.
	 * Fetches the permission data and fills the form for editing.
	 */
	#[Requires(ajax: true)]
	public function handleEdit(int $id): void
	{
		$items = $this->permissionsRepository->get($id)->record();
		$items ?: $this->error();

		if ($this->getSignal()) {
			$form = $this['factory'];
			$form->setDefaults($items);

			$buttonSend = $this->getFormComponent($form, 'send');
			$buttonSend->setCaption('Edit');
			$this->offCanvasComponent();
		}
	}


	/**
	 * Handles the delete action for a permission.
	 * Confirms the deletion before proceeding.
	 * @throws AttributeDetectionException
	 * @throws Exception
	 */
	#[Requires(ajax: true)]
	public function handleDelete(int $id): void
	{
		$items = $this->permissionsRepository->get($id)->record();
		$items ?: $this->error();

		$permissions = $this->rolesRepository
			->find(RolesEntity::PrimaryKey, $items->role_id)
			->record();

		$this->deleteItems = $permissions->name;
		$this->modalComponent();
	}


	/**
	 * Changes the permission status (Allow/Deny).
	 * Updates the permission in the repository and redraws the grid.
	 */
	public function statusChange(string $id, string $value): void
	{
		$id = (int) $id;
		$value = (int) $value;

		if ($id && $value >= 0) {
			$entity = new PermissionsEntity;
			$entity->id = $id;
			$entity->allowed = $value;

			$this->permissionsRepository->save($entity);
			$this->flashMessageOnPresenter('Authorization has been changed.');
			$this->redrawMessageOnPresenter();
			$this->redrawGrid();
		}
	}


	/**
	 * Creates a grid component for displaying the permissions.
	 * The grid includes columns for role, resource, privilege, and permission status.
	 * @throws AttributeDetectionException
	 * @throws DatagridColumnStatusException
	 * @throws DatagridException
	 */
	protected function createComponentGrid(string $name): DatagridComponent
	{
		$grid = new DatagridComponent($this, $name);
		$grid->setDataSource($this->permissionsViewRepository->getAll());

		if ($this->translator) {
			$grid->setTranslator($this->translator);
		}

		if ($this->templateGrid) {
			$grid->setTemplateFile($this->templateGrid);
		}

		$roles = $this->rolesRepository->getRolesPairs();
		$grid->addColumnText('role', 'Role')
			->setSortable()
			->setFilterSelect(array_merge([
				null => $grid->translateFilter('All'),
			], $roles));

		$grid->addColumnBase('resource', 'Resources');
		$grid->addColumnBase('privilege', 'Privileges');

		$expirationCol = $grid->addColumnStatus('allowed', 'Permission')
			->setSortable();

		$expirationCol->setCaret(false)
			->addOption(0, 'Denied')
			->setClass('btn-warning')
			->endOption()
			->addOption(1, 'Allowed')
			->setClass('btn-success')
			->endOption()
			->setFilterSelect([
				null => $grid->translateFilter('All'),
				0  => $grid->translateFilter('Denied'),
				1  => $grid->translateFilter('Allowed'),
			]);
		$expirationCol->onChange[] = [$this, 'statusChange'];
		$grid->addActionEdit('edit', 'Edit');
		$grid->addActionDeleteBase('delete', 'Delete');
		return $grid;
	}
}
