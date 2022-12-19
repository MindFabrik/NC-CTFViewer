<?php
namespace OCA\CtfViewer\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\BackgroundJob\IJobList;
use OCP\IConfig;
use OCP\IDateTimeFormatter;
use OCP\IL10N;
use OCP\Settings\ISettings;

class AdminSettings implements ISettings {

        /** @var IConfig */
        private $config;

        /** @var IL10N */
        private $l;

        /** @var IDateTimeFormatter */
        private $dateTimeFormatter;

        /** @var IJobList */
        private $jobList;

        /**
         * Admin constructor.
         *
         * @param Collector $collector
         * @param IConfig $config
         * @param IL10N $l
         * @param IDateTimeFormatter $dateTimeFormatter
         * @param IJobList $jobList
         */
        public function __construct(
                                                                IConfig $config,
                                                                IL10N $l,
                                                                IDateTimeFormatter $dateTimeFormatter,
                                                                IJobList $jobList
        ) {
                $this->config = $config;
                $this->l = $l;
                $this->dateTimeFormatter = $dateTimeFormatter;
                $this->jobList = $jobList;
        }

        /**
         * @return TemplateResponse
         */
        public function getForm() {

                return new TemplateResponse('ctfviewer', 'settings-admin');
        }

        /**
         * @return string the section ID, e.g. 'sharing'
         */
        public function getSection() {
                return 'ctfv';
        }

        /**
         * @return int whether the form should be rather on the top or bottom of
         * the admin section. The forms are arranged in ascending order of the
         * priority values. It is required to return a value between 0 and 100.
         */
        public function getPriority() {
                return 50;
        }

}