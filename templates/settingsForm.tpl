{**
 * plugins/generic/authorRequirements/templates/settingsForm.tpl
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Author requirements plugin settings
 *
 *}
<div id="authorRequirementsSettings">
<div id="description">{translate key="plugins.generic.authorRequirements.description"}</div>
<h3>{translate key="plugins.generic.authorRequirements.settings.title"}</h3>

<script>
    $(function() {ldelim}
        // Attach the form handler.
        $('#authorRequirementsSettingsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
    {rdelim});
</script>

<form class="pkp_form" id="authorRequirementsSettingsForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT op="manage" category="generic" plugin=$pluginName verb="settings" save=true}">
{csrf}

{fbvFormArea id="authorRequirementsSettingsForm"}
    {fbvFormSection list=true description="plugins.generic.authorRequirements.settings.description"}
        {fbvElement type="checkbox" id="emailOptional" value="1" checked=$emailOptional label="plugins.generic.authorRequirements.settings.emailOptional"}
    {/fbvFormSection}
{/fbvFormArea}

{fbvFormButtons id="authorRequirementsSettingsFormSubmit" submitText="common.save"}
</form>
</div>
