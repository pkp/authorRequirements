<?php

/**
 * @file plugins/generic/authorRequirements/AuthorRequirementsSettingsForm.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
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
        parent::initData();
    }

    /**
     * Assign form data to user-submitted data
     */
    public function readInputData() {
        $this->readUserVars(array('emailOptional'));
        parent::readInputData();
    }

    /**
     * Fetch the form.
     * @copydoc Form::fetch()
     */
    public function fetch($request, $template = null, $display = false) {
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('pluginName', $this->_plugin->getName());

        return parent::fetch($request, $template, $display);
    }

    /**
     * Save settings.
     */
    public function execute(...$functionArgs) {
        $plugin = $this->_plugin;
        $contextId = $this->_contextId;

        $plugin->updateSetting($contextId, 'emailOptional', $this->getData('emailOptional'), 'bool');
        return parent::execute(...$functionArgs);
    }
}
