<?php

/**
 * Drago Extension
 * Package built on Nette Framework
 */

declare(strict_types=1);

namespace Drago\Authorization\Tracy;

use Nette\Application\Application;
use Nette\Http\Request;
use Nette\Security\Permission;
use Nette\Security\SimpleIdentity;
use Nette\Security\User;
use Nette\SmartObject;
use Tracy\Debugger;
use Tracy\IBarPanel;


class Panel implements IBarPanel
{
	use SmartObject;

	public function __construct(
		private User $user,
		private Permission $permission,
		private Request $request,
		private Application $application,
		private PanelSession $panelSession,
	) {
		if (Debugger::$productionMode === false) {
			if ($this->request->getQuery('roleSwitchForm') === '1') {
				$identity = $user->getIdentity();
				if ($identity instanceof SimpleIdentity) {
					$roles = $this->request->getQuery('rolesList');
					$this->panelSession->save($roles);
					$identity->setRoles($roles ?: []);
				}

				$location = $this->request->getUrl()->getPath();
				header('Location: ' . $location);
				exit;
			}
		}
	}


	public function getTab(): string
	{
		$html = '<span title="Role switch">';
		$html .= '<svg version="1.0" xmlns="http://www.w3.org/2000/svg" width="512.000000pt" height="512.000000pt" viewBox="0 0 512.000000 512.000000" preserveAspectRatio="xMidYMid meet">';
		$html .= '<g transform="translate(0.000000,512.000000) scale(0.100000,-0.100000)" fill="#f12b2b" stroke="none">';
		$html .= '<path d="M3550 4420 c-65 -11 -127 -38 -190 -81 -104 -70 -223 -206 -668 -771 -94 -119 -103 -184 -35 -259 56 -62 142 -72 209 -25 12 9 88 100 170 203 198 251 397 495 417 512 16 12 17 -83 17 -1376 l0 -1390 23 -33 c32 -48 69 -72 119 -77 58 -7 118 23 149 74 l24 38 5 1384 5 1385 49 -55 c27 -30 158 -192 292 -360 259 -325 269 -335 350 -333 113 2 187 125 136 225 -26 51 -516 661 -603 750 -84 87 -179 152 -252 174 -64 19 -160 26 -217 15z"/>';
		$html .= '<path d="M1410 4188 c-19 -13 -45 -43 -57 -68 l-23 -44 0 -1376 -1 -1375 -41 45 c-23 25 -155 188 -295 363 -194 243 -262 322 -288 333 -122 51 -245 -50 -221 -181 10 -49 473 -633 621 -781 90 -91 124 -118 187 -149 69 -33 88 -38 171 -43 165 -8 266 36 413 183 115 114 603 725 621 778 37 106 -53 217 -165 204 -24 -3 -56 -13 -71 -24 -14 -11 -127 -146 -251 -301 -256 -321 -341 -422 -352 -422 -4 0 -8 621 -8 1381 0 1527 4 1423 -63 1471 -47 34 -132 36 -177 6z"/>';
		$html .= '</g></svg>';
		$html .= '<span class="tracy-label">Role switch</span>';
		$html .= '</span>';
		return $html;
	}


	public function getPanel(): string
	{
		ob_start();

		$user = $this->user;
		$permission = $this->permission;
		$request = $this->request;

		require __DIR__ . '/Panel.phtml';
		return (string) ob_get_clean();
	}
}
