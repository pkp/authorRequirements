{**
 * plugins/generic/authorRequirements/templates/controllers/grid/users/author/form/authorForm.tpl
 *
 * Copyright (c) 2014-2025 Simon Fraser University
 * Copyright (c) 2003-2025 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Submission Contributor grid form
 * @deprecated 3.4
 *
 *}

<script>
    $(function() {ldelim}
        $('#editAuthor').pkpHandler(
            '$.pkp.controllers.form.AjaxFormHandler'
        );
        {rdelim});
</script>

<form class="pkp_form" id="editAuthor" method="post" action="{url op="updateAuthor" authorId=$authorId}">
    {csrf}
    {include file="controllers/notification/inPlaceNotification.tpl" notificationId="authorFormNotification"}

    {include
    file="common/userDetails.tpl"
    disableUserNameSection=true
    disableAuthSourceSection=true
    disablePasswordSection=true
    disableSendNotifySection=true
    disableSalutationSection=true
    disableInitialsSection=true
    disablePhoneSection=true
    disableLocaleSection=true
    disableInterestsSection=true
    disableMailingSection=true
    disableSignatureSection=true
    extraContentSectionUnfolded=true
    countryRequired=true
    emailNotRequired=$emailNotRequired
    }

    {fbvFormArea id="submissionSpecific"}
    {if $requireAuthorCompetingInterests}
        {fbvFormSection title="author.competingInterests"}
        {fbvElement id="competingInterests" type="textarea" multilingual=true rich=true label="author.competingInterests.description" value=$competingInterests}
        {/fbvFormSection}
    {/if}
    {fbvFormSection id="userGroupId" title="submission.submit.contributorRole" list=true required=true}
    {foreach from=$authorUserGroups item=$userGroup}
        {if $userGroupId == $userGroup->getId()}{assign var="checked" value=true}{else}{assign var="checked" value=false}{/if}
        {fbvElement type="radio" id="userGroup"|concat:$userGroup->getId() name="userGroupId" value=$userGroup->getId() checked=$checked label=$userGroup->getLocalizedName() translate=false}
    {/foreach}
    {/fbvFormSection}
    {fbvFormSection list="true"}
    {fbvElement type="checkbox" label="submission.submit.selectPrincipalContact" id="primaryContact" checked=$primaryContact}
    {fbvElement type="checkbox" label="submission.submit.includeInBrowse" id="includeInBrowse" checked=$includeInBrowse}
    {$additionalCheckboxes}
    {/fbvFormSection}
    {/fbvFormArea}

    {if $submissionId}
        <input type="hidden" name="submissionId" value="{$submissionId|escape}" />
    {/if}
    {if $publicationId}
        <input type="hidden" name="publicationId" value="{$publicationId|escape}" />
    {/if}
    {if $gridId}
        <input type="hidden" name="gridId" value="{$gridId|escape}" />
    {/if}
    {if $rowId}
        <input type="hidden" name="rowId" value="{$rowId|escape}" />
    {/if}

    <p><span class="formRequired">{translate key="common.requiredField"}</span></p>
    {fbvFormButtons id="step2Buttons" submitText="common.save"}
</form>
