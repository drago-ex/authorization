<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Resources;

use Dibi\Exception;
use Drago\Application\UI\Alert;
use Drago\Attr\AttributeDetectionException;
use Drago\Authorization\Conf;
use Drago\Authorization\Control\Base;
use Drago\Authorization\Control\Component;
use Drago\Authorization\Control\Factory;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\Forms\Controls\BaseControl;
use Nette\SmartObject;
use Throwable;


/**
 * @property-read ResourcesTemplate $template
 */
class ResourcesControl extends Component implements Base
{
	use SmartObject;
	use Factory;

	public string $snippetFactory = 'resources';
	public string $snippetItems = 'resourcesItems';


	public function __construct(
		private Cache $cache,
		private ResourcesRepository $resourcesRepository,
	) {
	}


	public function render(): void
	{
		$template = $this->template;
		$template->setFile($this->templateFactory ?: __DIR__ . '/Resources.latte');
		$template->setTranslator($this->translator);
		$template->form = $this['factory'];
		$template->render();
	}


	/**
	 * @throws Exception
	 * @throws AttributeDetectionException
	 */
	public function renderItems(): void
	{
		$template = $this->template;
		$template->setFile($this->templateItems ?: __DIR__ . '/ResourcesItems.latte');
		$template->setTranslator($this->translator);
		$template->resources = $this->resourcesRepository->getAll();
		$template->deleteId = $this->deleteId;
		$template->render();
	}


	/**
	 * @throws BadRequestException
	 * @throws AttributeDetectionException
	 */
	public function handleEdit(int $id): void
	{
		$resource = $this->resourcesRepository->get($id)->fetch();
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
				$this->getPresenter()->redrawControl($this->snippetFactory);
			}
		}
	}


	/**
	 * @throws BadRequestException
	 * @throws Exception
	 * @throws AttributeDetectionException
	 */
	public function handleDelete(int $id): void
	{
		$resource = $this->resourcesRepository->getOne($id);
		$resource ?: $this->error();
		$this->deleteId = $resource->id;
		if ($this->isAjax()) {
			$this->getPresenter()
				->redrawControl($this->snippetItems);
		}
	}


	/**
	 * @throws BadRequestException
	 * @throws AttributeDetectionException
	 */
	public function handleDeleteConfirm(int $confirm, int $id): void
	{
		$resource = $this->resourcesRepository->get($id)->fetch();
		$resource ?: $this->error();

		if ($confirm === 1) {
			try {
				$this->resourcesRepository->remove($id);
				$this->cache->remove(Conf::CACHE);
				$this->getPresenter()->flashMessage(
					'Resource deleted.',
					Alert::DANGER,
				);

				$snippets = [
					$this->snippetFactory,
					$this->snippetItems,
					$this->snippetMessage,
					$this->snippetPermissions,
				];
				if ($this->isAjax()) {
					foreach ($snippets as $snippet) {
						$this->getPresenter()->redrawControl($snippet);
					}
				}

			} catch (Throwable $e) {
				if ($e->getCode() === 1451) {
					$this->getPresenter()->flashMessage(
						'The resource can not be deleted, you must first delete the records that are associated with it.',
						Alert::WARNING,
					);
					if ($this->isAjax()) {
						$this->getPresenter()
							->redrawControl($this->snippetMessage);
					}
				}
			}

		} else {
			if ($this->isAjax()) {
				$this->getPresenter()
					->redrawControl($this->snippetItems);
			}
		}
	}


	public function createComponentFactory(): Form
	{
		$form = $this->create();
		$form->addText(ResourcesData::NAME, 'Source')
			->setHtmlAttribute('placeholder', 'Source name')
			->setHtmlAttribute('autocomplete', 'off')
			->setRequired();

		$form->addHidden(ResourcesData::ID, 0)
			->addRule($form::INTEGER);

		$form->addSubmit('send', 'Send');
		$form->onSuccess[] = [$this, 'success'];
		return $form;
	}


	public function success(Form $form, ResourcesData $data): void
	{
		try {
			$this->resourcesRepository->put($data->toArray());
			$this->cache->remove(Conf::CACHE);

			$message = $data->id ? 'Resource updated.' : 'Resource inserted.';
			$this->getPresenter()->flashMessage($message, Alert::INFO);

			if ($this->isAjax()) {
				if ($data->id) {
					$this->getPresenter()->payload->close = 'close';
				}

				$snippets = [
					$this->snippetFactory,
					$this->snippetItems,
					$this->snippetMessage,
					$this->snippetPermissions,
				];
				foreach ($snippets as $snippet) {
					$this->getPresenter()->redrawControl($snippet);
				}
			}

			$form->reset();
			$formId = $form[ResourcesData::ID];
			if ($formId instanceof BaseControl) {
				$formId->setDefaultValue(0)
					->addRule($form::INTEGER);
			}

		} catch (Throwable $e) {
			$message = match ($e->getCode()) {
				1062 => 'This resource already exists.',
				default => 'Unknown status code.',
			};

			$form->addError($message);
			if ($this->isAjax()) {
				$this->getPresenter()->redrawControl($this->snippetFactory);
				$this->redrawControl($this->snippetError);
			}
		}
	}


	public function handleClickOpen()
	{
		if ($this->isAjax()) {
			$this->getPresenter()->payload->{$this->snippetFactory} = $this->snippetFactory;
			$this->getPresenter()->redrawControl($this->snippetFactory);
		}
	}
}
