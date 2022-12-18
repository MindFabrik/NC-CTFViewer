<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: MindFabrik UG <support@mindfabrik.de>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\CtfViewer\AppInfo;

use OCP\AppFramework\App;

class Application extends App {
	public const APP_ID = 'ctfviewer';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}
}
