<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization;

use Nette\Application\UI\Presenter;
use Nette\InvalidStateException;
use Nette\Security\User;


trait Authorization
{
	/**
	 * Checks for requirements such as authorization.
	 */
	public function injectAuthorization(Presenter $presenter, User $user): void
	{
		$presenter->onStartup[] = function () use ($presenter, $user) {
			try {
				$signal = $presenter->getSignal();
				if ((!empty($signal[0])) && isset($signal[1])) {
					if (!$user->isAllowed($presenter->getName(), $signal[0])) {
						$presenter->error('Forbidden', 403);
					}
				} else {
					if (!$user->isAllowed($presenter->getName(), $signal[1] ?? $presenter->getAction())) {
						$presenter->error('Forbidden', 403);
					}
				}
			} catch (InvalidStateException $e) {
				// Not implemented.
			}
		};
	}
}
