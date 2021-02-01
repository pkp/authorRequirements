<?php

/**
 * @file plugins/generic/authorRequirements/authorRequirementsPlugin.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class authorRequirementsPlugin
 * @ingroup plugins_generic_authorRequirements
 *
 * @brief Author Requirements plugin class
 */

import('lib.pkp.classes.plugins.GenericPlugin');
class AuthorRequirementsPlugin extends GenericPlugin {
    /**
     * Get the display name of this plugin
     * @return string
     */
    public function getDisplayName() {
        return __('plugins.generic.authorRequirements.displayName');
    }

    /**
     * Get the description of this plugin
     * @return string
     */
    public function getDescription() {
        return __('plugins.generic.authorRequirements.description');
    }

    /**
     * @copydoc Plugin::register()
     */
    public function register($category, $path, $mainContextId = NULL) {

        // Register the plugin even when it is not enabled
        $success = parent::register($category, $path);

        if ($success && $this->getEnabled()) {

            $contextId = $this->getCurrentContextId();

            // Deals with making email optional
            if ($this->getSetting($contextId, 'emailOptional')) {
                HookRegistry::register('TemplateResource::getFilename', array($this, '_overridePluginTemplates'));
                HookRegistry::register('TemplateManager::fetch', array(&$this, 'overrideFormDisplay'));
                HookRegistry::register('TemplateManager::fetch', array(&$this, 'overrideFormCreation'));
                HookRegistry::register('authorform::readuservars', array(&$this, 'overrideFormValidation'));
            }
        }
        return $success;
    }

    /**
     * Overrides visual presentation for required author form elements.
     */
    public function overrideFormDisplay($hookname, $args) {
        $templateMgr = $args[0];
        $template = $args[1];

        if ($template !== 'controllers/grid/users/author/form/authorForm.tpl') {
            return;
        }

        $templateMgr->assign('emailNotRequired', true);
    }

    /**
     * Overrides form creation regarding required and optional author form elements.
     */
    public function overrideFormCreation($hookname, $args) {

        $templateMgr = $args[0];
        $form = $templateMgr->getFBV()->getForm();

        $this->emailOverride($form);
    }

    /**
     * Overrides form validation for optional elements.
     */
    public function overrideFormValidation($hookname, $args) {
        $form = $args[0];
        $this->emailOverride($form);
    }

    /**
     * Overrides the email requirement for authors during form creation and validation.
     */
    public function emailOverride($form) {
        if (!is_a($form, 'AuthorForm')) {
            return;
        }

        // Remove email check from check list
        $checks =& $form->_checks;
        foreach ($checks as $k => $check) {
            if (is_a($check, 'FormValidatorEmail')) {
                unset($checks[$k]);
                $checks = array_values($checks);
                break;
            }
        }

        // Remove css validator element
        $cssValidation =& $form->cssValidation;
        unset($cssValidation['email']);

        // Add optional email form validation back in
        $form->addCheck(new FormValidatorEmail($form, 'email', 'optional'));
    }

    /**
     * @copydoc Plugin::getActions()
     */
    public function getActions($request, $verb) {
        $router = $request->getRouter();
        import('lib.pkp.classes.linkAction.request.AjaxModal');
        return array_merge(
            $this->getEnabled() ? array(
                new LinkAction(
                    'settings',
                    new AjaxModal(
                        $router->url($request, null, null, 'manage', null, array('verb' => 'settings', 'plugin' => $this->getName(), 'category' => 'generic')),
                        $this->getDisplayName()
                    ),
                    __('manager.plugins.settings'),
                    null
                ),
            ) : array(),
            parent::getActions($request, $verb)
        );
    }

    /**
     * @copydoc Plugin::manage()
     */
    public function manage($args, $request) {
        switch ($request->getUserVar('verb')) {

        // Return a JSON response containing the
        // settings form
        case 'settings':
            $this->import('AuthorRequirementsSettingsForm');
            $form = new AuthorRequirementsSettingsForm($this, $request->getContext()->getId());

            // Fetch the form the first time it loads,
            // before the user has tried to save it
            if ($request->getUserVar('save')) {
                $form->readInputData();
                if ($form->validate()) {
                    $form->execute();
                    $notificationMgr = new NotificationManager();
                    $notificationMgr->createTrivialNotification($request->getUser()->getId());
                    return new JSONMessage(true);
                }
            } else {
                $form->initData();
            }
            return new JSONMessage(true, $form->fetch($request));
        }
        return parent::manage($args, $request);
    }
}
