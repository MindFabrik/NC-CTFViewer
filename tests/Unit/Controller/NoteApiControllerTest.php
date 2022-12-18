<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: MindFabrik UG <support@mindfabrik.de>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\CtfViewer\Tests\Unit\Controller;

use OCA\CtfViewer\Controller\NoteApiController;

class NoteApiControllerTest extends NoteControllerTest {
	public function setUp(): void {
		parent::setUp();
		$this->controller = new NoteApiController($this->request, $this->service, $this->userId);
	}
}
