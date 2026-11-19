<?php

/**
 * @file plugins/generic/authorRequirements/AuthorRequirementsPlugin.php
 *
 * Copyright (c) 2014-2026 Simon Fraser University
 * Copyright (c) 2003-2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class AuthorRequirementsPlugin
 * @ingroup plugins_generic_authorRequirements
 *
 * @brief Author Requirements plugin class
 */

namespace APP\plugins\generic\authorRequirements;

use APP\controllers\grid\users\author\form\AuthorForm;
use APP\notification\NotificationManager;
use APP\template\TemplateManager;
use Illuminate\Support\Facades\Event;
use PKP\components\forms\Field;
use PKP\components\forms\publication\ContributorForm;
use PKP\core\JSONMessage;
use PKP\form\Form;
use PKP\form\validation\FormValidatorEmail;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use PKP\mail\Mailable;
use PKP\mail\variables\ContextEmailVariable;
use PKP\notification\Notification;
use PKP\observers\events\MessageSendingFromContext;
use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;

class AuthorRequirementsPlugin extends GenericPlugin
{
    /**
     * Get the display name of this plugin
     */
    public function getDisplayName(): string
    {
        return __('plugins.generic.authorRequirements.displayName');
    }

    /**
     * Get the description of this plugin
     */
    public function getDescription(): string
    {
        return __('plugins.generic.authorRequirements.description');
    }

    /**
     * @copydoc Plugin::register()
     */
    public function register($category, $path, $mainContextId = null)
    {

        // Register the plugin even when it is not enabled
        $success = parent::register($category, $path);

        if ($success && $this->getEnabled()) {
            $contextId = $this->getCurrentContextId();

            // Deals with making email optional
            if ($this->getSetting($contextId, 'emailOptional')) {
                // Component-based form
                Hook::add('Form::config::before', [$this, 'modifyContributorForm']);
                Hook::add('Schema::get::author', [$this, 'modifyAuthorSchema']);

                // Legacy forms (for Quick Submit plugin)
                Hook::add('TemplateResource::getFilename', [$this, '_overridePluginTemplates']);
                Hook::add('TemplateManager::fetch', [$this, 'overrideFormDisplay']);
                Hook::add('TemplateManager::fetch', [$this, 'overrideFormCreation']);
                Hook::add('authorform::readuservars', [$this, 'overrideFormValidation']);
            }
        }

        // Emails to authors without an email address. These check the email's own
        // context, because queued emails, such as ORCID verification requests, may be
        // sent by a CLI worker or during a request for another context.
        if ($success) {
            Hook::add('Mailable::build', [$this, 'removeRecipientsWithoutEmail']);
            Event::listen(MessageSendingFromContext::class, [$this, 'cancelMessageWithoutRecipients']);
        }
        return $success;
    }

    /**
     * Make contributor email optional in ContributorForm
     */
    public function modifyContributorForm($hookName, $form): bool
    {
        if (!$form instanceof ContributorForm) {
            return Hook::CONTINUE;
        }

        $form->fields = array_map(function (Field $field) {
            if ($field->name === 'email') {
                $field->isRequired = false;
            }

            return $field;
        }, $form->fields);

        return Hook::CONTINUE;
    }

    /**
     * Whether the plugin is enabled and author emails are optional in a context.
     *
     * Used for emails, which can be sent outside a request for their context, so the
     * current context's settings may not apply.
     */
    protected function isEmailOptional(int $contextId): bool
    {
        return $this->getEnabled($contextId) && (bool) $this->getSetting($contextId, 'emailOptional');
    }

    /**
     * Get the context of an email from its context variable, or null if the email
     * is not sent from a context.
     */
    protected function getMailableContextId(Mailable $mailable): ?int
    {
        foreach ($mailable->getVariables() as $variable) {
            if ($variable instanceof ContextEmailVariable) {
                return $variable->getContextFromVariable()->getId();
            }
        }

        return null;
    }

    /**
     * Remove recipients without an email address from an email before it is built,
     * so that emails sent to all of a submission's authors, such as the submission
     * acknowledgement, skip authors who have no email address.
     *
     * Only applies to emails from a context where the plugin is enabled and author
     * emails are optional.
     *
     * The hook passes the mailable as a named argument, so the parameter must be
     * called $mailable.
     */
    public function removeRecipientsWithoutEmail(string $hookName, Mailable $mailable): bool
    {
        $contextId = $this->getMailableContextId($mailable);
        if ($contextId === null || !$this->isEmailOptional($contextId)) {
            return Hook::CONTINUE;
        }

        foreach (['to', 'cc', 'bcc'] as $type) {
            $mailable->{$type} = array_values(
                array_filter($mailable->{$type}, fn (array $recipient) => !empty($recipient['address']))
            );
        }

        return Hook::CONTINUE;
    }

    /**
     * Cancel sending an email that has no recipients left after the recipients
     * without an email address were removed by removeRecipientsWithoutEmail().
     *
     * Only applies to emails from a context where the plugin is enabled and author
     * emails are optional. Returning false stops the email from being sent.
     * Returning null lets it send.
     */
    public function cancelMessageWithoutRecipients(MessageSendingFromContext $event): ?bool
    {
        $message = $event->message;

        return $this->isEmailOptional($event->context->getId())
            && empty($message->getTo()) && empty($message->getCc()) && empty($message->getBcc())
            ? false
            : null;
    }

    /**
     * Make email nullable in author schema
     */
    public function modifyAuthorSchema($hookName, $args): bool
    {
        $schema = &$args[0];
        $schema->required = array_filter($schema->required, fn ($item) => $item !== 'email');
        $schema->properties->email->validation[] = 'nullable';

        return Hook::CONTINUE;
    }

    /**
     * Overrides visual presentation for required author form elements.
     */
    public function overrideFormDisplay($hookname, $args): bool
    {
        /** @var TemplateManager $templateMgr */
        $templateMgr = $args[0];
        /** @var string $template */
        $template = $args[1];

        if ($template !== 'controllers/grid/users/author/form/authorForm.tpl') {
            return Hook::CONTINUE;
        }

        $templateMgr->assign('emailNotRequired', true);

        return Hook::CONTINUE;
    }

    /**
     * Overrides form creation regarding required and optional author form elements.
     */
    public function overrideFormCreation($hookname, $args): bool
    {

        /** @var TemplateManager $templateMgr */
        $templateMgr = $args[0];
        $form = $templateMgr->getFBV()->getForm();

        if ($form) {
            $this->emailOverride($form);
        }

        return Hook::CONTINUE;
    }

    /**
     * Overrides form validation for optional elements.
     */
    public function overrideFormValidation($hookname, $args): bool
    {
        /** @var Form $form */
        $form = $args[0];
        if ($form) {
            $this->emailOverride($form);
        }

        return Hook::CONTINUE;
    }

    /**
     * Overrides the email requirement for authors during form creation and validation.
     */
    public function emailOverride(Form $form): void
    {
        if (!($form instanceof AuthorForm)) {
            return;
        }

        // Remove email check from check list
        $checks =& $form->_checks;
        foreach ($checks as $k => $check) {
            if ($check instanceof FormValidatorEmail) {
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
    public function getActions($request, $actionArgs)
    {
        $actions = parent::getActions($request, $actionArgs);
        if (!$this->getEnabled()) {
            return $actions;
        }

        $router = $request->getRouter();
        $url = $router->url($request, null, null, 'manage', null, ['verb' => 'settings', 'plugin' => $this->getName(), 'category' => 'generic']);
        array_unshift($actions, new LinkAction('settings', new AjaxModal($url, $this->getDisplayName()), __('manager.plugins.settings')));
        return $actions;
    }

    /**
     * @copydoc Plugin::manage()
     */
    public function manage($args, $request): JSONMessage
    {
        if ($request->getUserVar('verb') !== 'settings') {
            return parent::manage($args, $request);
        }

        $form = new AuthorRequirementsSettingsForm($this, $request->getContext()->getId());
        if (!$request->getUserVar('save')) {
            $form->initData();
            return new JSONMessage(true, $form->fetch($request));
        }

        $form->readInputData();
        if (!$form->validate()) {
            return new JSONMessage(true, $form->fetch($request));
        }

        try {
            $form->execute();
            $notificationManager = new NotificationManager();
            $notificationManager->createTrivialNotification($request->getUser()->getId());
        } catch (\Exception $exception) {
            $notificationManager = new NotificationManager();
            $notificationManager->createTrivialNotification(
                $request->getUser()->getId(),
                Notification::NOTIFICATION_TYPE_ERROR,
                ['contents' => __('common.error.databaseError', ['error' => $exception->getMessage()])],
            );
        }
        return new JSONMessage(true);
    }
}
