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
use OCP\Mail\IMailer;

use \OC\Files\View;

class PageController extends Controller {

	private $userId;
	private $userView;
	protected $groupManager;
	private $mailer;

	public function __construct(IRequest $request,
										 $UserId,
										 IGroupManager $groupManager,
										 IMailer $mailer
	) 
	{
		parent::__construct(Application::APP_ID, $request);
		$this->userId = $UserId;
		$this->groupManager = $groupManager;
		$this->mailer = $mailer;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index(): TemplateResponse {
		
		// ----------------------------------------------
		// The index page is currently not in use and 
		// is a placeholder
		// ----------------------------------------------
		
		Util::addScript(Application::APP_ID, 'ctfviewer-main');

		return new TemplateResponse(Application::APP_ID, 'main');
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function edit($token, $file)
    {

		// ----------------------------------------------
		// The Edit page is the main entry when the user
		// is opening a CTF file. It will identify if the
		// user is part of the enterprise or a customer
		// (based on a NC group membership). In addition
		// the page will provide some page parameter for 
		// the edit.js script.
		// ----------------------------------------------

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

		// ----------------------------------------------
		// This json call returns the context of a CTF
		// file with additional metadata.
		// If the CTF file is empty, a CTF template 
		// will be returned instead.
		// ----------------------------------------------

		if (is_null($owner) || $owner === '') {
            $owner = \OC::$server->getUserSession()->getUser()->getUID();
        }

		$this->userView = new View('/' . $owner . '/files/');
        $fileInfo = $this->userView->getFileInfo($file);
		$content = $this->userView->file_get_contents($file);

		//if content = 0
		if (strlen($content) < 2) {
			$content = '{"remark":"BITTE ??FFNEN SIE DIESE DATEI IN DER CLOUD OBERFL??CHE","ctf_schema_version":1,"title":"Fehlende Lohn-Abrechnungsinformationen","lastModifiedMandant":"","lastEditorMandant":"","lastModifiedKanzlei":"","lastEditorKanzlei":"","taskList":[]}';
		} 

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

		// ----------------------------------------------
		// This json call receives the content of a CTF
		// file to save it into the NC folder.
		// ----------------------------------------------

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

	/**
	 *
	 * @NoCSRFRequired
     * @UseSession 
	 * @NoAdminRequired
	 * 
	 * @param string $emailTemplate
	 * @param string $receiver
	 * @param string $fileTitle
	 * @param string $taskTitle
     * @return JSONResponse
	 * 
	 */
    public function sendEmailNotification($emailTemplate,$receiver,$fileTitle,$taskTitle,$user) {

		// ----------------------------------------------
		// This json call is used from the edit.js script
		// to send a notificiation to the user, when e.g.,
		// a new tasks is defined.
		// The logic, when a notification is pushed, is 
		// part of the edit.js script.
		// ----------------------------------------------

		// ----------------------------------------------
		// Prepare template
		// ----------------------------------------------
		if ($emailTemplate == "NewTask") {
			$subject = "Neue Aufgabe vom Steuerberater eingetragen";
			$heading = $subject;
			$bodytxt = "Ihr Steuerberater hat Ihnen die neue Aufgabe '" . $taskTitle . "' in die '" . $fileTitle . "' Datei eingetragen. Bitte bearbeiten Sie diese bei der n??chsten M??glichkeit und reichen Sie ggf. die fehlenden Unterlagen ??ber die Cloud ein."; 
		}
		else if ($emailTemplate == "NewReady") {
			$subject = "Mandant " . $user . " hat eine Aufgabe abgeschlossen";
			$heading = $subject;
			$bodytxt = "Ihr Mandant " . $user . " hat die Aufgabe '" . $taskTitle . "' als erledigt gekennzeichnet. Die Details entnehmen Sie bitte der '" . $fileTitle . "' Datei im Mandantenordner " . $user;
		}
		else {

			// Return error, wrong email template
			$params = array(
				'result' => 'FAILED',
				'error' => 'Wrong email template'			
			);
	
			return new JSONResponse($params);			
		}

		// ----------------------------------------------
		// Send email
		// ----------------------------------------------
		$template = $this->mailer->createEMailTemplate('settings.TestEmail', [
					'receiver' => $receiver
					]);
	
		$template->setSubject($subject);
		$template->addHeader();
		$template->addHeading($heading);
		$template->addBodyText($bodytxt);
		$template->addFooter();
	
		$message = $this->mailer->createMessage();
		$message->setTo(array($receiver => $receiver));
		$message->useTemplate($template);
		$errors = $this->mailer->send($message);
					

		$params = array(
			'result' => 'OK'			
		);

        return new JSONResponse($params);
    }

}
