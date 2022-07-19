<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Control\Roles;

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
use Nette\Forms\Controls\SelectBox;
use Nette\SmartObject;
use Throwable;


/**
 * @property-read RolesTemplate $template
 */
class RolesControl extends Component implements Base
{
	use SmartObject;
	use Factory;

	public string $snippetFactory = 'roles';
	public string $snippetItems = 'rolesItems';


	public function __construct(
		private Cache $cache,
		private RolesRepository $rolesRepository,
	) {
	}


	public function render(): void
	{
		$template = $this->template;
		$template->setFile($this->templateFactory ?: __DIR__ . '/Roles.latte');
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
		$template->setFile($this->templateItems ?: __DIR__ . '/RolesItems.latte');
		$template->setTranslator($this->translator);
		$template->roles = $this->getRecords();
		$template->deleteId = $this->deleteId;
		$template->render();
	}


	/**
	 * @throws Exception
	 * @throws BadRequestException
	 * @throws AttributeDetectionException
	 */
	public function handleEdit(int $id): void
	{
		$role = $this->rolesRepository->getRole($id);
		$role ?: $this->error();

		try {
			if ($this->rolesRepository->isAllowed($role->name) && $this->getSignal()) {
				$form = $this['factory'];
				if ($form instanceof Form) {
					$form->setDefaults($role);
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
					'The role is not allowed to be updated.',
					Alert::WARNING,
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
		$role = $this->rolesRepository->getRole($id);
		$role ?: $this->error();
		$this->deleteId = $role->id;
		if ($this->isAjax()) {
			$this->getPresenter()
				->redrawControl($this->snippetItems);
		}
	}


	/**
	 * @throws AttributeDetectionException
	 * @throws BadRequestException
	 * @throws Exception
	 */
	public function handleDeleteConfirm(int $confirm, int $id): void
	{
		$role = $this->rolesRepository->getRole($id);
		$role ?: $this->error();

		if ($confirm === 1) {
			try {
				$parent = $this->rolesRepository->findParent($id);
				if (!$parent && $this->rolesRepository->isAllowed($role->name)) {
					$this->rolesRepository->remove($id);
					$this->cache->remove(Conf::CACHE);
					$this->getPresenter()->flashMessage('Role deleted.', Alert::DANGER);
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
					1001 => 'The role is not allowed to be deleted.',
					1002 => 'The role cannot be deleted because it is bound to another role.',
					1451 => 'The role can not be deleted, you must first delete the records that are associated with it.',
					default => 'Unknown status code.',
				};

				$this->getPresenter()->flashMessage($message, Alert::WARNING);
				if ($this->isAjax()) {
					$this->getPresenter()->redrawControl($this->snippetMessage);
				}
			}

		} else {
			if ($this->isAjax()) {
				$this->getPresenter()
					->redrawControl($this->snippetItems);
			}
		}
	}


	/**
	 * @throws AttributeDetectionException
	 */
	public function createComponentFactory(): Form
	{
		$form = $this->create();
		$form->addText(RolesData::NAME, 'Role')
			->setHtmlAttribute('placeholder', 'Role name')
			->setHtmlAttribute('autocomplete', 'nope')
			->setRequired();

		if ($this->getSignal()) {
			$id = (int) $this->getParameter('id');
			foreach ($this->rolesRepository->getRoles() as $key => $item) {
				if ($id !== $key) {
					$roles[$key] = $item;
				}
			}
		}

		$form->addSelect(RolesData::PARENT, 'Parent', $roles ?? $this->rolesRepository->getRoles())
			->setPrompt('Select parent')
			->setRequired();

		$form->addHidden(RolesData::ID, 0)
			->addRule($form::INTEGER);

		$form->addSubmit('send', 'Send');
		$form->onSuccess[] = [$this, 'success'];
		return $form;
	}


	public function success(Form $form, RolesData $data): void
	{
		try {
			$this->rolesRepository->put($data->toArray());
			$this->cache->remove(Conf::CACHE);

			$parent = $this['factory']['parent'];
			if ($parent instanceof SelectBox) {
				$parent->setItems($this->rolesRepository->getRoles());
			}

			$message = $data->id ? 'Role updated.' : 'The role was inserted.';
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
			$formId = $form[RolesData::ID];
			if ($formId instanceof BaseControl) {
				$formId->setDefaultValue(0)
					->addRule($form::INTEGER);
			}

		} catch (Throwable $e) {
			$message = match ($e->getCode()) {
				1062 => 'This role already exists.',
				default => 'Unknown status code.',
			};

			$form->addError($message);
			if ($this->isAjax()) {
				$this->getPresenter()->redrawControl($this->snippetFactory);
			}
		}
	}


	/**
	 * @return RolesEntity[]
	 * @throws AttributeDetectionException
	 * @throws Exception
	 */
	public function getRecords(): array
	{
		$roles = [];
		foreach ($this->rolesRepository->getAll() as $role) {
			$parent = $this->rolesRepository->findByParent($role->parent);
			$role->parent = $parent->name ?? 'none';
			$roles[] = $role;
		}
		return $roles;
	}


	public function handleClickOpen()
	{
		if ($this->isAjax()) {
			$this->getPresenter()->payload->{$this->snippetFactory} = $this->snippetFactory;
			$this->getPresenter()->redrawControl($this->snippetFactory);
		}
	}
}
