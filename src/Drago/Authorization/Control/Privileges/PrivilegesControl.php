<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Privileges;

use Dibi\Exception;
use Drago\Application\UI\Alert;
use Drago\Attr\AttributeDetectionException;
use Drago\Authorization\Conf;
use Drago\Authorization\Control\Base;
use Drago\Authorization\Control\Component;
use Drago\Authorization\Control\Factory;
use Drago\Authorization\NotAllowedChange;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\Forms\Controls\BaseControl;
use Nette\SmartObject;
use Throwable;


/**
 * @property-read PrivilegesTemplate $template
 */
class PrivilegesControl extends Component implements Base
{
	use SmartObject;
	use Factory;

	public string $snippetFactory = 'privileges';
	public string $snippetItems = 'privilegesItems';


	public function __construct(
		private Cache $cache,
		private PrivilegesRepository $privilegesRepository,
	) {
	}


	public function render(): void
	{
		$template = $this->template;
		$template->setFile($this->templateFactory ?: __DIR__ . '/Privileges.latte');
		$template->setTranslator($this->translator);
		$template->form = $this['factory'];
		$template->render();
	}


	/**
	 * @throws AttributeDetectionException
	 * @throws Exception
	 */
	public function renderItems(): void
	{
		$template = $this->template;
		$template->setFile($this->templateItems ?: __DIR__ . '/PrivilegesItems.latte');
		$template->setTranslator($this->translator);
		$template->privileges = $this->privilegesRepository->getAll();
		$template->deleteId = $this->deleteId;
		$template->render();
	}


	/**
	 * @throws BadRequestException
	 * @throws Exception
	 * @throws AttributeDetectionException
	 */
	public function handleEdit(int $id): void
	{
		$privilege = $this->privilegesRepository->getOne($id);
		$privilege ?: $this->error();

		try {
			if ($this->privilegesRepository->isAllowed($privilege->name) && $this->getSignal()) {
				$form = $this['factory'];
				if ($form instanceof Form) {
					$form->setDefaults($privilege);
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

		} catch (NotAllowedChange $e) {
			if ($e->getCode() === 1001) {
				$this->getPresenter()->flashMessage(
					'The privilege is not allowed to be updated.',
					Alert::WARNING
				);

				if ($this->isAjax()) {
					$this->getPresenter()
						->redrawControl($this->snippetMessage);
				}
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
		$privilege = $this->privilegesRepository->getOne($id);
		$privilege ?: $this->error();
		$this->deleteId = $privilege->id;
		if ($this->isAjax()) {
			$this->getPresenter()
				->redrawControl($this->snippetItems);
		}
	}


	/**
	 * @throws BadRequestException
	 * @throws Exception
	 * @throws AttributeDetectionException
	 */
	public function handleDeleteConfirm(int $confirm, int $id): void
	{
		$privilege = $this->privilegesRepository->getOne($id);
		$privilege ?: $this->error();

		if ($confirm === 1) {
			try {
				if ($this->privilegesRepository->isAllowed($privilege->name)) {
					$this->privilegesRepository->remove($id);
					$this->cache->remove(Conf::CACHE);
					$this->getPresenter()->flashMessage(
						'Privilege deleted.',
						Alert::DANGER
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
				}

			} catch (Throwable $e) {
				$message = match ($e->getCode()) {
					1001 => 'The privilege is not allowed to be deleted.',
					1451 => 'The privilege can not be deleted, you must first delete the records that are associated with it.',
					default => 'Unknown status code.',
				};

				$this->getPresenter()
					->flashMessage($message, Alert::WARNING);

				if ($this->isAjax()) {
					$this->getPresenter()
						->redrawControl($this->snippetMessage);
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
		$form->addText(PrivilegesData::NAME, 'Action or signal')
			->setHtmlAttribute('placeholder', 'Name action or signal')
			->setHtmlAttribute('autocomplete', 'off')
			->setRequired();

		$form->addHidden(PrivilegesData::ID, 0)
			->addRule($form::INTEGER);

		$form->addSubmit('send', 'Send');
		$form->onSuccess[] = [$this, 'success'];
		return $form;
	}


	public function success(Form $form, PrivilegesData $data): void
	{
		try {
			$this->privilegesRepository->put($data->toArray());
			$this->cache->remove(Conf::CACHE);

			$message = $data->id ? 'Privilege updated.' : 'Privilege inserted.';
			$this->getPresenter()->flashMessage($message);

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
			$formId = $form[PrivilegesData::ID];
			if ($formId instanceof BaseControl) {
				$formId->setDefaultValue(0)
					->addRule($form::INTEGER);
			}

		} catch (Throwable $e) {
			$message = match ($e->getCode()) {
				1062 => 'This privilege already exists.',
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
