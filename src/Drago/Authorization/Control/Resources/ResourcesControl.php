<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Resources;

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
use Drago\Authorization\Datagrid\DatagridComponent;
use Nette\Application\AbortException;
use Nette\Application\Attributes\Requires;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Throwable;


/**
 * @property-read ComponentTemplate $template
 */
class ResourcesControl extends Component implements Base
{
	use Factory;

	public string $snippetFactory = 'resources';


	public function __construct(
		private readonly Cache $cache,
		private readonly ResourcesRepository $resourcesRepository,
	) {
	}


	public function render(): void
	{
		$template = $this->createRender();
		$template->setFile($this->templateControl ?: __DIR__ . '/Resources.latte');
		$template->render();
	}


	#[Requires(ajax: true)]
	public function handleClickOpenComponent(): void
	{
		$this->offCanvasComponent();
	}


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
		$form->onSuccess[] = [$this, 'success'];
		return $form;
	}


	/**
	 * @throws AbortException
	 */
	#[Requires(ajax: true)]
	public function success(Form $form, ResourcesData $data): void
	{
		try {
			$this->resourcesRepository->save($data->toArray());
			$this->cache->remove(Conf::Cache);

			$message = $data->id ? 'Resource updated.' : 'Resource inserted.';
			$this->getPresenter()->flashMessage($message, Alert::Info);

			if ($data->id) {
				$this->getPresenter()->payload->close = 'close';
			}
			$this->redrawControlMessage();
			$this->redrawControl($this->snippetFactory);
			$this['grid']->reload();
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

		try {
			$this->resourcesRepository->delete(ResourcesEntity::PrimaryKey, $items->id)->execute();
			$this->cache->remove(Conf::Cache);
			$this->getPresenter()->flashMessage('Resource deleted.', Alert::Danger);
			$this->redrawControlMessage();
			$this['grid']->reload();

		} catch (Throwable $e) {
			$message = match ($e->getCode()) {
				1451 => 'The resource can not be deleted, you must first delete the records that are associated with it',
				default => 'Unknown status code.',
			};

			$this->getPresenter()->flashMessage($message, Alert::Warning);
			$this->redrawControlMessage();
		}
	}


	/**
	 * @throws AttributeDetectionException
	 * @throws DataGridException
	 */
	protected function createComponentGrid($name): DataGrid
	{
		$grid = new DatagridComponent($this, $name);
		$grid->setDataSource($this->resourcesRepository->getAll());
		$grid->init();

		if ($this->templateGrid) {
			$grid->setTemplateFile($this->templateGrid);
		}

		$grid->addColumnBase('name', 'Name');
		$grid->addActionEdit('edit', 'Edit');
		$grid->addActionDelete('delete', 'Delete');
		return $grid;
	}
}
