<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization;

use Nette\Application\UI\Presenter;
use Nette\Security\User;


trait Authorization
{
	/**
	 * Checks for requirements such as authorization.
	 */
	public function injectAuthorization(Presenter $presenter, User $user): void
	{
		$presenter->onStartup[] = function () use ($presenter, $user) {
			$signal = $presenter->getSignal();
			if ($signal === null) {
				$signal = $presenter->getAction();

			} elseif (!empty($signal[0])) {
				$signal = "$signal[0]-$signal[1]";

			} else {
				$signal = $signal[1];
			}

			if (!$user->isAllowed($presenter->getName(), $signal)) {
				$presenter->error('Forbidden', 403);

			}
		};
	}
}
