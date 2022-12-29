<?php

namespace OCA\CTFViewer\Migration;

use OCP\Migration\IOutput;
use OCP\Migration\IRepairStep;
use OCP\ILogger;

class RegisterMimeType implements IRepairStep {
    protected $logger;
    private $customMimetypeMapping;

    public function __construct(ILogger $logger) {
        $this->logger = $logger;

        // Define the custom mimetype mapping
        $this->customMimetypeMapping = array(
            "ctf" => array("application/ctf")
        );
    }

    public function getName() {
        return 'Register MIME type for "application/ctf"';
    }

    private function registerForExistingFiles() {
        $mimetypeMapping = $this->customMimetypeMapping;
        $mimeTypeLoader = \OC::$server->getMimeTypeLoader();

        $this->logger->info('Add CTF mimetype in file cache...');
        foreach($mimetypeMapping as $mimetypeKey => $mimetypeValues) {
            foreach($mimetypeValues as $mimetypeValue) {
                $mimeId = $mimeTypeLoader->getId($mimetypeValue);
                $mimeTypeLoader->updateFilecache($mimetypeKey, $mimeId);
            }
        }
    }

    private function registerForNewFiles() {
        $mimetypeMapping = $this->customMimetypeMapping;
        $mimetypeMappingFile = \OC::$configDir . 'mimetypemapping.json';
        $this->logger->info('Prepare to add CTF mimetype in ' . $mimetypeMappingFile);

        if (file_exists($mimetypeMappingFile)) {
            $this->logger->info('Mimetypemapping.json found - add CTF...');
            $existingMimetypeMapping = json_decode(file_get_contents($mimetypeMappingFile), true);
            $mimetypeMapping = array_merge($existingMimetypeMapping, $mimetypeMapping);
        }
        else {
            $this->logger->info("Mimetypemapping.json not found!");
        }

        file_put_contents($mimetypeMappingFile, json_encode($mimetypeMapping, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }

    public function run(IOutput $output) {
        $this->logger->info('Registering the CTF mimetype...');

        // Register the mime type for existing files
        $this->registerForExistingFiles();

        // Register the mime type for new files
        $this->registerForNewFiles();

        $this->logger->info('The CTF mimetype was successfully registered.');
    }
}