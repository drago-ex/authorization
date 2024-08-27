<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Privileges;

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
use Drago\Authorization\NotAllowedChange;
use Nette\Application\AbortException;
use Nette\Application\Attributes\Requires;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\SmartObject;
use Throwable;


/**
 * @property-read ComponentTemplate $template
 */
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


	public function render(): void
	{
		$template = $this->createRender();
		$template->setFile($this->templateControl ?: __DIR__ . '/Privileges.latte');
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
		$form->addText(PrivilegesEntity::ColumnName, 'Action or signal')
			->setHtmlAttribute('placeholder', 'Name action or signal')
			->setHtmlAttribute('autocomplete', 'off')
			->setRequired();

		$form->addHidden(PrivilegesEntity::PrimaryKey)
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
	public function success(Form $form, PrivilegesData $data): void
	{
		try {
			$this->privilegesRepository->save($data->toArray());
			$this->cache->remove(Conf::Cache);

			$message = $data->id ? 'Privilege updated.' : 'Privilege inserted.';
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
				1062 => 'This privilege already exists.',
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
		$items = $this->privilegesRepository->get($id)->record();
		$items ?: $this->error();

		try {
			if ($this->privilegesRepository->isAllowed($items->name)) {
				$form = $this['factory'];
				$form->setDefaults($items);

				$buttonSend = $this->getFormComponent($form, 'send');
				$buttonSend->setCaption('Edit');
				$this->offCanvasComponent();
			}

		} catch (NotAllowedChange $e) {
			$message = match ($e->getCode()) {
				1001 => 'The privilege is not allowed to be updated.',
				default => 'Unknown status code.',
			};

			$this->getPresenter()->flashMessage($message, Alert::Warning);
			$this->redrawControlMessage();
		}
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
		$items = $this->privilegesRepository->get($id)->record();
		$items ?: $this->error();

		try {
			if ($this->privilegesRepository->isAllowed($items->name)) {
				$this->privilegesRepository->delete(PrivilegesEntity::PrimaryKey, $items->id)->execute();
				$this->cache->remove(Conf::Cache);
				$this->getPresenter()->flashMessage(
					'Privilege deleted.',
					Alert::Danger,
				);

				$this->redrawControlMessage();
				$this['grid']->reload();
			}

		} catch (Throwable $e) {
			$message = match ($e->getCode()) {
				1001 => 'The privilege is not allowed to be deleted.',
				1451 => 'The privilege can not be deleted, you must first delete the records that are associated with it.',
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
		$grid = new DataGrid($this, $name);
		$grid->setDataSource($this->privilegesRepository->getAll());

		if ($this->translator) {
			$grid->setTranslator($this->translator);
		}

		if ($this->templateGrid) {
			$grid->setTemplateFile($this->templateGrid);
		}

		$grid->addColumnText('name', 'Name')
			->setSortable()
			->setFilterText();

		$grid->addAction('edit', 'Edit')
			->setClass('btn btn-xs btn-primary text-white ajax');

		$confirm = 'Are you sure you want to delete the selected item?';
		if ($this->translator) {
			$confirm = $this->translator->translate($confirm);
		}
		$grid->addAction('delete', 'Delete')
			->setClass('btn btn-xs btn-danger ajax')
			->setConfirmation(new StringConfirmation($confirm));

		return $grid;
	}
}
