<?php

declare(strict_types=1);

namespace Drago\Authorization;

use Nette\Application\UI\Presenter;
use Nette\Security\User;


/** @property-read string $loginLink */
trait Authorization
{
	/** Checks for requirements such as authorization. */
	public function injectAuthorization(Presenter $presenter, User $user): void
	{
		$presenter->onStartup[] = function () use ($presenter, $user) {
			$signal = $presenter->getSignal();

			// Resolve the signal for the current action
			if ($signal === null) {
				$signal = $presenter->getAction();
			} elseif (!empty($signal[0])) {
				$signal = "$signal[0]-$signal[1]";
			} else {
				$signal = $signal[1];
			}

			// Check user authorization for the current signal
			if (!$user->isAllowed($presenter->getName(), $signal)) {
				if (!$user->isLoggedIn()) {
					// If not logged in, redirect to login page
					$presenter->redirect($this->loginLink, [
						'backlink' => $presenter->storeRequest(),
					]);
				} else {
					// If logged in but not authorized, show forbidden error
					$presenter->error('Forbidden', 403);
				}
			}
		};
	}
}
