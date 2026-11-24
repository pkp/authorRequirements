{**
 * plugins/generic/authorRequirements/templates/authorFormEmail.tpl
 *
 * Copyright (c) 2026 Simon Fraser University
 * Copyright (c) 2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Email section of the author form. The author form disables the email section
 * of common/userDetails.tpl and adds this one instead, so the email can be optional.
 *
 * Parameters:
 *   $emailNotRequired: Whether or not email is a required field
 *}

{fbvFormArea id="authorRequirementsEmail"}
    {fbvFormSection title="about.contact"}
        {fbvElement type="email" label="user.email" id="email" required=!$emailNotRequired value=$email maxlength="90" size=$fbvStyles.size.MEDIUM}
    {/fbvFormSection}
{/fbvFormArea}
