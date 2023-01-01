<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: MindFabrik UG <support@mindfabrik.de>
// SPDX-License-Identifier: AGPL-3.0-or-later

return [
	'routes' => [
		['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
		['name' => 'page#edit', 'url' => '/edit', 'verb' => 'GET'],
		['name' => 'page#getctf', 'url' => '/json/getctf', 'verb' => 'POST'],
		['name' => 'page#setctf', 'url' => '/json/setctf', 'verb' => 'POST'],
		['name' => 'page#sendEmailNotification', 'url' => '/json/sendEmailNotification', 'verb' => 'POST'],		
	]
];
