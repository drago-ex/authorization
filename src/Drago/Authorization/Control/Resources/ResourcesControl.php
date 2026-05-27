<?php

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
use Nette\Application\Attributes\Requires;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\Forms\Controls\SubmitButton;
use Throwable;


/** @property-read ComponentTemplate $template */
class ResourcesControl extends Component implements Base
{
	use Factory;

	public string $snippetFactory = 'resources';


	public function __construct(
		private readonly Cache $cache,
		private readonly ResourcesRepository $resourcesRepository,
	) {
	}


	/** Renders the template for the resources control. */
	public function render(): void
	{
		$template = $this->createRender();
		$template->setFile($this->templateControl ?: __DIR__ . '/Resources.latte');
		$template->render();
	}


	/** Handles the AJAX request to open the component. */
	#[Requires(ajax: true)]
	public function handleClickOpenComponent(): void
	{
		$this->offCanvasComponent();
	}


	/** Creates the delete form. */
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


	/** Deletes a resource and shows the result in a flash message. */
	public function delete(Form $form, int $id): void
	{
		try {
			$this->resourcesRepository
				->delete(ResourcesEntity::PrimaryKey, $id)
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


	/** Creates the form to add or edit a resource. */
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
		$form->onSuccess[] = function (Form $form, ResourcesValues $data): void {
			$this->success($form, $data);
		};
		return $form;
	}


	private function success(Form $form, ResourcesValues $data): void
	{
		try {
			$this->resourcesRepository->save((array) $data);
			$this->cache->remove(Conf::Cache);

			$message = isset($data->id) ? 'Resource updated.' : 'Resource inserted.';
			$this->flashMessageOnPresenter($message, Alert::Success);

			if (isset($data->id)) {
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


	/** Handles the AJAX request to edit a resource.
	 * @throws AttributeDetectionException
	 * @throws Exception
	 */
	#[Requires(ajax: true)]
	public function handleEdit(int $id): void
	{
		$items = $this->resourcesRepository->get($id)->record();
		if ($items !== null) {
			$form = $this['factory'];
			$form->setDefaults($items);

			$buttonSend = $this->getFormComponent($form, 'send');
			$buttonSend?->setCaption('Edit');
			$this->offCanvasComponent();
		}
	}


	/** Handles the AJAX request to delete a resource.
	 * @throws AttributeDetectionException
	 * @throws Exception
	 */
	#[Requires(ajax: true)]
	public function handleDelete(int $id): void
	{
		$items = $this->resourcesRepository->get($id)->record();
		if ($items !== null) {
			$this->deleteItems = $items->name;
			$this->modalComponent();
		}
	}


	/** Creates the grid component for displaying resources.
	 * @throws DatagridException
	 * @throws AttributeDetectionException
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
