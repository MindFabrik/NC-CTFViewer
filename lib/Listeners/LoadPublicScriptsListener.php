<?php

declare(strict_types=1);

namespace OCA\CtfViewer\Listeners;

use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Util;

class LoadPublicScriptsListener implements IEventListener {
	public function handle(Event $event): void {
		Util::addScript('ctfviewer', 'js/index.js');
	}
}