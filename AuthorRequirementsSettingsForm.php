<?php

/**
 * @file plugins/generic/authorRequirements/AuthorRequirementsSettingsForm.php
 *
 * Copyright (c) 2014-2026 Simon Fraser University
 * Copyright (c) 2003-2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class AuthorRequirementsSettingsForm
 * @ingroup plugins_generic_authorRequirements
 *
 * @brief Form for managers to modify Author Requirements plugin settings
 */

namespace APP\plugins\generic\authorRequirements;

use APP\plugins\generic\authorRequirements\classes\migration\AuthorRequirementsSchemaMigration;
use APP\template\TemplateManager;
use PKP\form\Form;
use PKP\form\validation\FormValidatorCSRF;
use PKP\form\validation\FormValidatorPost;

class AuthorRequirementsSettingsForm extends Form
{
    private int $contextId;
    private AuthorRequirementsPlugin $plugin;

    public function __construct(AuthorRequirementsPlugin $plugin, int $contextId)
    {
        $this->contextId = $contextId;
        $this->plugin = $plugin;

        parent::__construct($plugin->getTemplateResource('settingsForm.tpl'));
        $this->addCheck(new FormValidatorPost($this));
        $this->addCheck(new FormValidatorCSRF($this));
    }

    /**
     * Initialize form data
     */
    public function initData()
    {
        $contextId = $this->contextId;
        $plugin = $this->plugin;

        $this->setData('emailOptional', $plugin->getSetting($contextId, 'emailOptional'));
        parent::initData();
    }

    /**
     * Assign form data to user-submitted data
     */
    public function readInputData()
    {
        $this->readUserVars(array('emailOptional'));
        parent::readInputData();
    }

    /**
     * Fetch the form.
     * @copydoc Form::fetch()
     */
    public function fetch($request, $template = null, $display = false)
    {
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('pluginName', $this->plugin->getName());

        return parent::fetch($request, $template, $display);
    }

    /**
     * Save settings.
     */
    public function execute(...$functionArgs)
    {
        $plugin = $this->plugin;
        $contextId = $this->contextId;

        $isEmailOptional = $this->getData('emailOptional');

        // Sites that upgraded the plugin may not have run the install migration yet
        if ($isEmailOptional) {
            (new AuthorRequirementsSchemaMigration())->up();
        }

        $plugin->updateSetting($contextId, 'emailOptional', $isEmailOptional, 'bool');
        return parent::execute(...$functionArgs);
    }
}
