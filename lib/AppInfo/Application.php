<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: MindFabrik UG <support@mindfabrik.de>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\CTFViewer\AppInfo;

use OCP\AppFramework\App;
use OCP\EventDispatcher\IEventDispatcher; 
use OCA\Files\Event\LoadAdditionalScriptsEvent; 
use OCP\Util;

class Application extends App {
	public const APP_ID = 'ctfviewer';

	public function __construct() {
		parent::__construct(self::APP_ID);
		
		// this runs every time Nextcloud loads a page if this app is enabled
		$container = $this->getContainer();
		$eventDispatcher = $container->get(IEventDispatcher::class);
		
		
		// load files plugin script when the Files app triggers the LoadAdditionalScriptsEvent event
		$eventDispatcher->addListener(LoadAdditionalScriptsEvent::class, function () {
		// this loads the js/filesplugin.js script once the Files app has done loading its scripts
		Util::addscript(self::APP_ID, 'filesplugin', 'files');
	});
	}

	public function register() {

		$server = $this->getContainer()->getServer();

		/** @var IMimeTypeDetector $mimeTypeDetector */
		$mimeTypeDetector = $server->query(IMimeTypeDetector::class);

		/** @var IEventDispatcher $eventDispatcher */
		$eventDispatcher = $server->query(IEventDispatcher::class);

		// registerType without getAllMappings will prevent loading nextcloud's default mappings.
		$mimeTypeDetector->getAllMappings();
		$mimeTypeDetector->registerType('ctf', 'application/ctf', null);

		// Watch Viewer load event
		//$eventDispatcher->addServiceListener(LoadViewer::class, LoadFiles3dScript::class);
		//$eventDispatcher->addServiceListener(AddContentSecurityPolicyEvent::class, CSPListener::class);
	}
}
