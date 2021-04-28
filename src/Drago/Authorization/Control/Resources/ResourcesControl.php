<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control;

use Dibi\Exception;
use Drago\Application\UI\Alert;
use Drago\Authorization\Conf;
use Drago\Authorization\Data\ResourcesData;
use Drago\Authorization\Repository\ResourcesRepository;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\Forms\Controls\BaseControl;


class ResourcesControl extends Component implements Base
{
	public string $snippetFactory = 'resources';
	public string $snippetRecords = 'resourcesRecords';


	public function __construct(
		private Cache $cache,
		private ResourcesRepository $repository,
	) {
	}


	public function render(): void
	{
		$template = $this->template;
		$template->form = $this['factory'];
		$template->setFile(__DIR__ . '/Templates/Resources.add.latte');
		$template->render();
	}


	/**
	 * @throws Exception
	 */
	public function renderRecords(): void
	{
		$template = $this->template;
		$template->resources = $this->repository->getAll();
		$template->deleteId = $this->deleteId;
		$template->setFile(__DIR__ . '/Templates/Resources.records.latte');
		$template->render();
	}


	/**
	 * @throws BadRequestException
	 */
	public function handleEdit(int $id): void
	{
		$resource = $this->repository->get($id)->fetch();
		$resource ?: $this->error();

		if ($this->getSignal()) {

			/** @var Form|BaseControl $form */
			$form = $this['factory'];
			$form['send']->caption = 'Edit';
			$form->setDefaults($resource);

			if ($this->isAjax()) {
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
		$resource = $this->repository->getRecord($id);
		$resource ?: $this->error();
		$this->deleteId = $resource->id;

		if ($this->isAjax()) {
			$this->redrawPresenter($this->snippetRecords);
		}
	}


	/**
	 * @throws BadRequestException
	 */
	public function handleDeleteConfirm(int $confirm, int $id): void
	{
		$resource = $this->repository->get($id)->fetch();
		$resource ?: $this->error();

		if ($confirm === 1) {
			try {
				$this->repository->erase($id);
				$this->cache->remove(Conf::CACHE);
				$this->flashMessagePresenter('Resource deleted.', Alert::DANGER);

				if ($this->isAjax()) {
					$this->redrawPresenter($this->snippetFactory);
					$this->redrawPresenter($this->snippetRecords);
					$this->redrawPresenter($this->snippetMessage);
					$this->redrawPresenter($this->snippetPermissions);
				}

			} catch (\Exception $e) {
				if ($e->getCode() === 1451) {
					$this->flashMessagePresenter('The resource can not be deleted, you must first delete the records that are associated with it', Alert::WARNING);
					if ($this->isAjax()) {
						$this->redrawPresenter($this->snippetMessage);
					}
				}
			}

		} else {
			if ($this->isAjax()) {
				$this->redrawPresenter($this->snippetRecords);
			}
		}
	}


	public function createComponentFactory(): Form
	{
		$form = new Form;
		$form->addText(ResourcesData::NAME, 'Source')
			->setHtmlAttribute('placeholder', 'Source name')
			->setHtmlAttribute('autocomplete', 'off')
			->setRequired();

		$form->addHidden(ResourcesData::ID, 0)
			->addRule(Form::INTEGER);

		$form->addSubmit('send', 'Send');
		$form->onSuccess[] = [$this, 'success'];
		return $form;
	}


	public function success(Form $form, ResourcesData $data): void
	{
		try {
			$form->reset();

			/** @var Form|BaseControl $formId */
			$formId = $form[ResourcesData::ID];
			$formId->setDefaultValue(0)
				->addRule(Form::INTEGER);

			$this->repository->put($data->toArray());
			$this->cache->remove(Conf::CACHE);

			$message = $data->id ? 'Resource updated.' : 'Resource inserted.';
			$this->flashMessagePresenter($message);

			if ($this->isAjax()) {
				$this->redrawPresenter($this->snippetFactory);
				$this->redrawPresenter($this->snippetRecords);
				$this->redrawPresenter($this->snippetMessage);
				$this->redrawPresenter($this->snippetPermissions);
			}

		} catch (\Exception $e) {
			if ($e->getCode() === 1062) {
				$form->addError('This resource already exists.');
			}

			if ($this->isAjax()) {
				$this->redrawPresenter($this->snippetFactory);
				$this->redrawControl($this->snippetError);
			}
		}
	}
}
