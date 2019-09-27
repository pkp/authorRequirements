<?php

/**
 * @file plugins/generic/authorRequirements/AuthorRequirementsSettingsForm.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorRequirementsSettingsForm
 * @ingroup plugins_generic_authorRequirements
 *
 * @brief Form for managers to modify Author Requirements plugin settings
 */

import('lib.pkp.classes.form.Form');

class AuthorRequirementsSettingsForm extends Form {

    /** @var int Associated context ID */
	private $_contextId;

	/** @var AuthorRequirementsPlugin Author requirements plugin */
	private $_plugin;

	/**
	 * Constructor
	 * @param $plugin AuthorRequirementsPlugin Author requirements plugin
	 * @param $contextId int Context ID
	 */
    function __construct($plugin, $contextId) {
		$this->_contextId = $contextId;
		$this->_plugin = $plugin;

		parent::__construct($plugin->getTemplateResource('settingsForm.tpl'));
		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorCSRF($this));
	}

    /**
     * Initialize form data
     */
    public function initData() {
        $contextId = $this->_contextId;
        $plugin = $this->_plugin;
        
        $this->setData('emailOptional', $plugin->getSetting($contextId, 'emailOptional'));
        // parent::initData(); // TODO: See if needed
    }

    /**
     * Assign form data to user-submitted data
     */
    public function readInputData() {
        $this->readUserVars(array('emailOptional'));
        // parent::readInputData(); // TODO: See if needed
    }

    /**
     * Fetch the form.
     * @copydoc Form::fetch()
     */
    public function fetch($request) {
        $templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('pluginName', $this->_plugin->getName());

        return parent::fetch($request);
    }

    /**
     * Save settings.
     */
    public function execute() {
        $plugin = $this->_plugin;
        $contextId = $this->_contextId;
        
        $plugin->updateSetting($contextId, 'emailOptional', $this->getData('emailOptional'), 'bool');
        // return parent::execute();
    }  
}