<?php

declare(strict_types=1);

namespace Drago\Authorization;

use Nette\Application\UI\Presenter;
use Nette\Security\User;


trait Authorization
{
	public string $loginLink;


	/** Checks for requirements such as authorization. */
	public function injectAuthorization(Presenter $presenter, User $user): void
	{
		$presenter->onStartup[] = function () use ($presenter, $user) {
			$resource = $this->resolveAclResource($presenter);

			if (!$user->isAllowed($presenter->getName(), $resource)) {
				if (!$user->isLoggedIn()) {
					$presenter->redirect($this->loginLink, [
						'backlink' => $presenter->storeRequest(),
					]);
				} else {
					$presenter->error('Forbidden', 403);
				}
			}
		};
	}


	/**
	 * Resolves ACL resource name from the current presenter action or signal.
	 */
	protected function resolveAclResource(Presenter $presenter): string
	{
		$signal = $presenter->getSignal();

		if ($signal === null) {
			return $presenter->getAction();
		}

		[$receiver, $name] = $signal;
		return $receiver ? "$receiver-$name" : $name;
	}
}
