<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Resources;

use Dibi\Exception;
use Drago\Application\UI\Alert;
use Drago\Authorization\Conf;
use Drago\Authorization\Control\Base;
use Drago\Authorization\Control\Component;
use Drago\Authorization\Service\Data\ResourcesData;
use Drago\Authorization\Service\Repository\ResourcesRepository;
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
		$template = __DIR__ . '/Templates/Resources.add.latte';
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
		$template = __DIR__ . '/Templates/Resources.records.latte';
		$template = $this->templateRecords ?: $template;
		$resources = $this->repository->getAll();
		$items = [
			'resources' => $resources,
			'deleteId' => $this->deleteId,
		];

		$this->setRenderControl($template, $items);
	}


	/**
	 * @throws BadRequestException
	 */
	public function handleEdit(int $id): void
	{
		$resource = $this->repository->get($id)->fetch();
		$resource ?: $this->error();

		if ($this->getSignal()) {
			$form = $this['factory'];
			if ($form instanceof Form) {
				$form->setDefaults($resource);
			}


			$buttonSend = $form['send'];
			if ($buttonSend instanceof BaseControl) {
				$buttonSend->setCaption('Edit');
			}

			if ($this->isAjax()) {
				$this->getPresenter()->payload->{$this->snippetFactory} = $this->snippetFactory;
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
					$this->multipleRedrawPresenter([
						$this->snippetFactory,
						$this->snippetRecords,
						$this->snippetMessage,
						$this->snippetPermissions,
					]);
				}

			} catch (\Throwable $e) {
				if ($e->getCode() === 1451) {
					$this->flashMessagePresenter('The resource can not be deleted, you must first delete the records that are associated with it.', Alert::WARNING);
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
		$form = $this->factory();
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

			$formId = $form[ResourcesData::ID];
			if ($formId instanceof BaseControl) {
				$formId->setDefaultValue(0)
					->addRule(Form::INTEGER);
			}

			$this->repository->put($data->toArray());
			$this->cache->remove(Conf::CACHE);

			$message = $data->id ? 'Resource updated.' : 'Resource inserted.';
			$this->flashMessagePresenter($message);

			if ($this->isAjax()) {
				$this->multipleRedrawPresenter([
					$this->snippetFactory,
					$this->snippetRecords,
					$this->snippetMessage,
					$this->snippetPermissions,
				]);
			}

		} catch (\Throwable $e) {
			$message = match ($e->getCode()) {
				1062 => 'This resource already exists.',
				default => 'Unknown status code.',
			};

			$form->addError($message);
			if ($this->isAjax()) {
				$this->redrawPresenter($this->snippetFactory);
				$this->redrawControl($this->snippetError);
			}
		}
	}


	public function handleClickOpen()
	{
		if ($this->isAjax()) {
			$this->getPresenter()->payload->{$this->snippetFactory} = $this->snippetFactory;
			$this->redrawPresenter($this->snippetFactory);
		}
	}
}
