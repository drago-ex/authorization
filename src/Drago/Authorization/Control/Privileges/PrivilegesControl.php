<?php

declare(strict_types=1);

namespace Drago\Authorization\Control\Privileges;

use Contributte\Datagrid\Exception\DatagridException;
use Dibi\Exception;
use Drago\Application\UI\Alert;
use Drago\Attr\AttributeDetectionException;
use Drago\Authorization\Conf;
use Drago\Authorization\Control\Base;
use Drago\Authorization\Control\Component;
use Drago\Authorization\Control\DatagridComponent;
use Drago\Authorization\Control\Factory;
use Drago\Authorization\NotAllowedChange;
use Nette\Application\Attributes\Requires;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\Forms\Controls\SubmitButton;
use Nette\SmartObject;
use Throwable;


/** Privileges control class responsible for managing privileges and their CRUD operations. */
class PrivilegesControl extends Component implements Base
{
	use SmartObject;
	use Factory;

	public string $snippetFactory = 'privileges';


	public function __construct(
		private readonly Cache $cache,
		private readonly PrivilegesRepository $privilegesRepository,
	) {
	}


	/** Renders the template for the privileges control. */
	public function render(): void
	{
		$template = $this->createRender();
		$template->setFile($this->templateControl ?: __DIR__ . '/Privileges.latte');
		$template->render();
	}


	/** Opens the component off-canvas (used in AJAX requests). */
	#[Requires(ajax: true)]
	public function handleClickOpenComponent(): void
	{
		$this->offCanvasComponent();
	}


	/** Creates the delete confirmation form. */
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


	public function delete(Form $form, int $id): void
	{
		try {
			$this->privilegesRepository
				->delete(PrivilegesEntity::PrimaryKey, $id)
				->execute();

			$this->cache->remove(Conf::Cache);
			$this->flashMessageOnPresenter('Privilege deleted.');
			$this->closeComponent();
			$this->redrawDeleteFactoryAll();

		} catch (Throwable $e) {
			$message = match ($e->getCode()) {
				1451 => 'The privilege can not be deleted, you must first delete the records that are associated with it.',
				default => 'Unknown status code.',
			};
			$this->flashMessageOnPresenter($message, Alert::Warning);
			$this->redrawMessageOnPresenter();
		}
	}


	/** Creates the form for adding or editing privileges. */
	protected function createComponentFactory(): Form
	{
		$form = $this->create();
		$form->addText(PrivilegesEntity::ColumnName, 'Action or signal')
			->setHtmlAttribute('placeholder', 'Name action or signal')
			->setHtmlAttribute('autocomplete', 'off')
			->setRequired();

		$form->addHidden(PrivilegesEntity::PrimaryKey)
			->addRule($form::Integer)
			->setNullable();

		$form->addSubmit('send', 'Send');
		$form->onSuccess[] = function (Form $form, PrivilegesValues $data): void {
			$this->success($form, $data);
		};
		return $form;
	}


	private function success(Form $form, PrivilegesValues $data): void
	{
		try {
			$this->privilegesRepository->save((array) $data);
			$this->cache->remove(Conf::Cache);

			$message = isset($data->id) ? 'Privilege updated.' : 'Privilege inserted.';
			$this->flashMessageOnPresenter($message, Alert::Success);

			if (isset($data->id)) {
				$this->closeComponent();
			}
			$this->redrawSuccessFactory();
			$form->reset();

		} catch (Throwable $e) {
			$message = match ($e->getCode()) {
				1062 => 'This privilege already exists.',
				default => 'Unknown status code.',
			};

			$form->addError($message);
			$this->redrawControl($this->snippetFactory);
		}
	}


	/** Handles the editing of a privilege by pre-filling the form with existing data.
	 * @throws AttributeDetectionException
	 * @throws Exception
	 */
	#[Requires(ajax: true)]
	public function handleEdit(int $id): void
	{
		$items = $this->privilegesRepository->get($id)->record();
		if ($items !== null) {
			try {
				if ($this->privilegesRepository->isAllowed($items->name)) {
					$form = $this['factory'];
					$form->setDefaults($items);

					$buttonSend = $this->getFormComponent($form, 'send');
					$buttonSend?->setCaption('Edit');
					$this->offCanvasComponent();
				}

			} catch (NotAllowedChange $e) {
				$message = match ($e->getCode()) {
					1001 => 'The privilege is not allowed to be updated.',
					default => 'Unknown status code.',
				};

				$this->flashMessageOnPresenter($message, Alert::Warning);
				$this->redrawMessageOnPresenter();
			}
		}
	}


	/** Handles the deletion of a privilege.
	 * @throws AttributeDetectionException
	 * @throws Exception
	 */
	#[Requires(ajax: true)]
	public function handleDelete(int $id): void
	{
		$items = $this->privilegesRepository->get($id)->record();
		if ($items !== null) {
			try {
				if ($this->privilegesRepository->isAllowed($items->name)) {
					$this->deleteItems = $items->name;
					$this->modalComponent();
				}

			} catch (Throwable $e) {
				$message = match ($e->getCode()) {
					1001 => 'The privilege is not allowed to be deleted.',
					default => 'Unknown status code.',
				};

				$this->flashMessageOnPresenter($message, Alert::Warning);
				$this->redrawMessageOnPresenter();
			}
		}
	}


	/** Creates the data grid for listing privileges.
	 * @throws DatagridException
	 * @throws AttributeDetectionException
	 */
	protected function createComponentGrid(string $name): DatagridComponent
	{
		$grid = new DatagridComponent($this, $name);
		$grid->setDataSource($this->privilegesRepository->getAll());

		if ($this->translator) {
			$grid->setTranslator($this->translator);
		}

		if ($this->templateGrid) {
			$grid->setTemplateFile($this->templateGrid);
		}

		$grid->addColumnBase('name', 'Name');
		$grid->addActionEdit('edit', 'Edit');
		$grid->addActionDeleteBase('delete', 'Delete');
		return $grid;
	}
}
