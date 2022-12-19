<?php
namespace OCA\CtfViewer\Settings;

use OCP\IL10N;
use OCP\Settings\IIconSection;
use OCP\IURLGenerator;

class AdminSection implements IIconSection {

	/** @var IL10N */
	private $l;
	/** @var IURLGenerator */
	private $url;

	/**
	 * @param IURLGenerator $url
	 * @param IL10N $l
	 */
	public function __construct(IURLGenerator $url, IL10N $l) {
		$this->url = $url;
		$this->l = $l;
	}

        /**
	    * {@inheritdoc}
	    */
        public function getID() {
                return 'ctfv'; 
        }

        /**
        * {@inheritdoc}
	    */
        public function getName() {
                return 'CTF Viewer';
        }

        /**
	    * {@inheritdoc}
	    */
        public function getPriority() {
                return 80;
        }

        /**
	    * {@inheritdoc}
	    */
	    public function getIcon() {
		    return $this->url->imagePath('ctfviewer', 'app.svg');
	    }

}