<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Roles;

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
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\Forms\Controls\SelectBox;
use Nette\SmartObject;
use Throwable;


/**
 * @property-read ComponentTemplate $template
 */
class RolesControl extends Component implements Base
{
	use SmartObject;
	use Factory;

	public string $snippetFactory = 'roles';


	public function __construct(
		private readonly Cache $cache,
		private readonly RolesRepository $rolesRepository,
	) {
	}


	public function render(): void
	{
		$template = $this->template;
		$template->setFile($this->templateControl ?: __DIR__ . '/Roles.latte');
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
	public function createComponentFactory(): Form
	{
		$form = $this->create();
		$form->addText(RolesEntity::Name, 'Role')
			->setHtmlAttribute('placeholder', 'Role name')
			->setHtmlAttribute('autocomplete', 'off')
			->setRequired();

		if ($this->getSignal()) {
			$id = (int) $this->getParameter('id');
			foreach ($this->rolesRepository->getRoles() as $key => $item) {
				if ($id !== $key) {
					$roles[$key] = $item;
				}
			}
		}

		$form->addSelect(RolesEntity::Parent, 'Parent', $roles ?? $this->rolesRepository->getRoles())
			->setPrompt('Select parent')
			->setRequired();

		$form->addHidden(RolesEntity::Id)
			->addRule($form::INTEGER)
			->setNullable();

		$form->addSubmit('send', 'Send');
		$form->onSuccess[] = [$this, 'success'];
		return $form;
	}


	/**
	 * @throws AbortException
	 */
	public function success(Form $form, RolesData $data): void
	{
		try {
			if ($data->id !== null && $data->id < $data->parent) {
				throw new \Exception('It is not allowed to select a higher parent.', 1);
			}

			$this->rolesRepository->save($data);
			$this->cache->remove(Conf::Cache);

			$parent = $this['factory']['parent'];
			if ($parent instanceof SelectBox) {
				$parent->setItems($this->rolesRepository->getRoles());
			}

			$message = $data->id ? 'Role updated.' : 'The role was inserted.';
			$this->getPresenter()->flashMessage($message, Alert::Info);

			if ($this->isAjax()) {
				if ($data->id) {
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
				1 => $e->getMessage(),
				1062 => 'This role already exists.',
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
	 * @throws BadRequestException
	 * @throws Exception
	 */
	public function handleEdit(int $id): void
	{
		$items = $this->rolesRepository->getOne($id);
		$items ?: $this->error();

		try {
			if ($this->rolesRepository->isAllowed($items->name)) {
				$form = $this['factory'];
				$form->setDefaults($items);

				$buttonSend = $this->getFormComponent($form, 'send');
				$buttonSend->setCaption('Edit');

				if ($this->isAjax()) {
					$component = $this->getUniqueComponent($this->openComponentType);
					$this->getPresenter()->payload->{$this->openComponentType} = $component;
					$this->redrawControl($this->snippetFactory);

				} else {
					$this->redirect('this');
				}
			}

		} catch (NotAllowedChange $e) {
			$message = match ($e->getCode()) {
				1001 => 'The role is not allowed to be updated.',
				default => 'Unknown status code.',
			};

			$this->getPresenter()
				->flashMessage($message, Alert::Warning);

			$this->isAjax()
				? $this->getPresenter()->redrawControl($this->snippetMessage)
				: $this->redirect('this');
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
		$items = $this->rolesRepository->getOne($id);
		$items ?: $this->error();

		try {
			$parent = $this->rolesRepository->findParent($items->id);
			if (!$parent && $this->rolesRepository->isAllowed($items->name)) {
				$this->rolesRepository->remove($id);
				$this->cache->remove(Conf::Cache);
				$this->getPresenter()->flashMessage('Role deleted.', Alert::Danger);

				if ($this->isAjax()) {
					$this->getPresenter()->redrawControl($this->snippetMessage);
					$this['grid']->reload();

				} else {
					$this->redirect('this');
				}
			}

		} catch (Throwable $e) {
			$message = match ($e->getCode()) {
				1001 => 'The role is not allowed to be deleted.',
				1002 => 'The role cannot be deleted because it is bound to another role.',
				default => 'Unknown status code.',
			};

			$this->getPresenter()
				->flashMessage($message, Alert::Warning);

			$this->isAjax()
				? $this->getPresenter()->redrawControl($this->snippetMessage)
				: $this->redirect('this');
		}
	}


	/**
	 * @throws AttributeDetectionException
	 * @throws DataGridException
	 */
	protected function createComponentGrid($name): DataGrid
	{
		$grid = new DataGrid($this, $name);
		$grid->setDataSource($this->rolesRepository->getAll());

		if ($this->translator) {
			$grid->setTranslator($this->translator);
		}

		if ($this->templateGrid) {
			$grid->setTemplateFile($this->templateGrid);
		}

		$grid->addColumnText('name', 'Name')
			->setSortable()
			->setFilterText();

		$grid->addColumnText('parent', 'Parent')
			->setSortable()
			->setRenderer(fn(RolesEntity $item) => $this->rolesRepository->findByParent($item->parent)->name ?? null)->setFilterText();

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
