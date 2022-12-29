<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: MindFabrik UG <support@mindfabrik.de>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\CTFViewer\Controller;

use OCA\CTFViewer\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use OCP\Util;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IGroupManager;
use OCP\IGroup;

use \OC\Files\View;

class PageController extends Controller {

	private $userId;
	private $userView;
	protected $groupManager;

	public function __construct(IRequest $request,
										 $UserId,
										 IGroupManager $groupManager
	) 
	{
		parent::__construct(Application::APP_ID, $request);
		$this->userId = $UserId;
		$this->groupManager = $groupManager;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index(): TemplateResponse {
		Util::addScript(Application::APP_ID, 'ctfviewer-main');

		return new TemplateResponse(Application::APP_ID, 'main');
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function edit($token, $file)
    {

		$usrInfo = \OC::$server->getUserSession()->getUser();
		$grpInfo = $this->groupManager->getUserGroups($usrInfo);
		$editor = "NO";
		foreach ($grpInfo as $grp) {
			$grpName = $grp->getDisplayName();
			if ($grpName == "admin" || $grpName == "Kanzlei-Mitarbeiter") {
				$editor = "YES";
			}
		}
		$usrId = \OC::$server->getUserSession()->getUser()->getUID();
		$params['username'] = $usrId;
		$params['dateplaceholder'] = date("m/Y");

        if (!empty($token)) {

			if ($token != "EMPTY") {
				$share = $this->shareManager->getShareByToken($token);
				$fileowner = $share->getShareOwner();
	
				// Setup filesystem
				$nodes = $this->rootFolder->getUserFolder($fileowner)->getById($share->getNodeId());
				$pfile = array_shift($nodes);
				$path = $pfile->getPath();
				$segments = explode('/', trim($path, '/'), 3);
				$startPath = $segments[2];
	
				$filename = $startPath . '/' . rawurldecode($file);
	
				\OC::$server->getSession()->close();	

				$params['token'] = 'YES';
				$params['owner'] = $fileowner;
				$params['file'] = $filename;
				$params['editor'] = $editor;
				return new TemplateResponse(Application::APP_ID, 'edit', $params);
			}
			else {
				$params['token'] = 'NO';
				$params['owner'] = $this->userId;
				$params['file'] = rawurldecode($file);
				$params['editor'] = $editor;
				return new TemplateResponse(Application::APP_ID, 'edit', $params);
			}
        }
    }

	 /**
	 *
	 * @NoCSRFRequired
     * @UseSession 
	 * @NoAdminRequired
	 * 
	 * @param string $owner
	 * @param string $file
     * @return JSONResponse
	 * 
	 */
    public function getctf($owner,$file) {

		if (is_null($owner) || $owner === '') {
            $owner = \OC::$server->getUserSession()->getUser()->getUID();
        }

		$this->userView = new View('/' . $owner . '/files/');
        $fileInfo = $this->userView->getFileInfo($file);
		$content = $this->userView->file_get_contents($file);

		if (str_contains($content,"ctf_schema_version")) {
			$params = array(
				'result' => 'OK',
				'size' => $fileInfo['size'],
				'json' => $content
			);
		}
		else {
			$params = array(
				'result' => 'ERROR',
				'error' => 'Unexpected CTF Format'
			);
		}

		

        return new JSONResponse($params);
    }

	/**
	 *
	 * @NoCSRFRequired
     * @UseSession 
	 * @NoAdminRequired
	 * 
	 * @param string $owner
	 * @param string $file
	 * @param string $content
     * @return JSONResponse
	 * 
	 */
    public function setctf($owner,$file,$content) {


		if (is_null($owner) || $owner === '') {
            $owner = \OC::$server->getUserSession()->getUser()->getUID();
        }

		$this->userView = new View('/' . $owner . '/files/');
        $fileInfo = $this->userView->getFileInfo($file);
		$result = $this->userView->file_put_contents($file,$content);

		$params = array(
			'result' => 'OK'			
		);

        return new JSONResponse($params);
    }

}
