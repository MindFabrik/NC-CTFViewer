<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: MindFabrik UG <support@mindfabrik.de>
// SPDX-License-Identifier: AGPL-3.0-or-later

/**
 * Create your routes in here. The name is the lowercase name of the controller
 * without the controller part, the stuff after the hash is the method.
 * e.g. page#index -> OCA\CTFViewer\Controller\PageController->index()
 *
 * The controller class has to be registered in the application.php file since
 * it's instantiated in there
 */
return [
	'routes' => [
		['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
		['name' => 'page#edit', 'url' => '/edit', 'verb' => 'GET'],
		['name' => 'page#getctf', 'url' => '/json/getctf', 'verb' => 'POST'],
		['name' => 'page#setctf', 'url' => '/json/setctf', 'verb' => 'POST'],
		['name' => 'page#sendEmailNotification', 'url' => '/json/sendEmailNotification', 'verb' => 'POST'],		
	]
];
