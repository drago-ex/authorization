<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Resources;

use App\Authorization\Control\ComponentTemplate;
use Contributte\Datagrid\Exception\DatagridException;
use Dibi\Exception;
use Drago\Application\UI\Alert;
use Drago\Attr\AttributeDetectionException;
use Drago\Authorization\Conf;
use Drago\Authorization\Control\Base;
use Drago\Authorization\Control\Component;
use Drago\Authorization\Control\DatagridComponent;
use Drago\Authorization\Control\Factory;
use Nette\Application\AbortException;
use Nette\Application\Attributes\Requires;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Throwable;


/**
 * This control manages resources: allows adding, editing, deleting, and viewing resources.
 *
 * @property-read ComponentTemplate $template
 */
class ResourcesControl extends Component implements Base
{
	use Factory;

	public string $snippetFactory = 'resources';


	/**
	 * Constructor for ResourcesControl.
	 *
	 * @param Cache $cache
	 * @param ResourcesRepository $resourcesRepository
	 */
	public function __construct(
		private readonly Cache $cache,
		private readonly ResourcesRepository $resourcesRepository,
	) {
	}


	/**
	 * Renders the template for the resources control.
	 */
	public function render(): void
	{
		$template = $this->createRender();
		$template->setFile($this->templateControl ?: __DIR__ . '/Resources.latte');
		$template->render();
	}


	/**
	 * Handles the AJAX request to open the component.
	 */
	#[Requires(ajax: true)]
	public function handleClickOpenComponent(): void
	{
		$this->offCanvasComponent();
	}


	/**
	 * Creates the delete form.
	 */
	protected function createComponentDelete(): Form
	{
		$form = $this->createDelete($this->id);
		$form->addSubmit('confirm', 'Confirm')
			->onClick[] = $this->delete(...);
		return $form;
	}


	/**
	 * Deletes a resource and shows the result in a flash message.
	 */
	public function delete(Form $form, \stdClass $data): void
	{
		try {
			$this->resourcesRepository
				->delete(ResourcesEntity::PrimaryKey, $data->id)
				->execute();

			$this->cache->remove(Conf::Cache);
			$this->flashMessageOnPresenter('Resource deleted.');
			$this->closeComponent();
			$this->redrawDeleteFactoryAll();

		} catch (Throwable $e) {
			$message = match ($e->getCode()) {
				1451 => 'The resource can not be deleted, you must first delete the records that are associated with it.',
				default => 'Unknown status code.',
			};
			$this->flashMessageOnPresenter($message, Alert::Warning);
			$this->redrawMessageOnPresenter();
		}
	}


	/**
	 * Creates the form to add or edit a resource.
	 */
	protected function createComponentFactory(): Form
	{
		$form = $this->create();
		$form->addText(ResourcesEntity::ColumnName, 'Source')
			->setHtmlAttribute('placeholder', 'Source name')
			->setHtmlAttribute('autocomplete', 'off')
			->setRequired();

		$form->addHidden(ResourcesEntity::PrimaryKey)
			->addRule($form::Integer)
			->setNullable();

		$form->addSubmit('send', 'Send');
		$form->onSuccess[] = $this->success(...);
		return $form;
	}


	/**
	 * Handles the success of the add/edit form, saving the resource.
	 *
	 * @throws AbortException
	 */
	private function success(Form $form, ResourcesData $data): void
	{
		try {
			$this->resourcesRepository->save($data->toArray());
			$this->cache->remove(Conf::Cache);

			$message = $data->id ? 'Resource updated.' : 'Resource inserted.';
			$this->flashMessageOnPresenter($message, Alert::Success);

			if ($data->id) {
				$this->closeComponent();
			}
			$this->redrawSuccessFactory();
			$form->reset();

		} catch (Throwable $e) {
			$message = match ($e->getCode()) {
				1062 => 'This resource already exists.',
				default => 'Unknown status code.',
			};

			$form->addError($message);
			$this->redrawControl($this->snippetFactory);
		}
	}


	/**
	 * Handles the AJAX request to edit a resource.
	 *
	 * @throws AbortException
	 * @throws AttributeDetectionException
	 * @throws BadRequestException
	 * @throws Exception
	 */
	#[Requires(ajax: true)]
	public function handleEdit(int $id): void
	{
		$items = $this->resourcesRepository->get($id)->record();
		$items ?: $this->error();

		$form = $this['factory'];
		$form->setDefaults($items);

		$buttonSend = $this->getFormComponent($form, 'send');
		$buttonSend->setCaption('Edit');
		$this->offCanvasComponent();
	}


	/**
	 * Handles the AJAX request to delete a resource.
	 *
	 * @throws AbortException
	 * @throws AttributeDetectionException
	 * @throws BadRequestException
	 * @throws Exception
	 */
	#[Requires(ajax: true)]
	public function handleDelete(int $id): void
	{
		$items = $this->resourcesRepository->get($id)->record();
		$items ?: $this->error();

		$this->deleteItems = $items->name;
		$this->modalComponent();
	}


	/**
	 * Creates the grid component for displaying resources.
	 *
	 * @throws AttributeDetectionException
	 * @throws DataGridException
	 */
	protected function createComponentGrid(string $name): DatagridComponent
	{
		$grid = new DatagridComponent($this, $name);
		$grid->setDataSource($this->resourcesRepository->getAll());

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
