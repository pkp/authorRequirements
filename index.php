<?php

/**
 * @defgroup plugins_generic_authorRequirements Author Requirements Plugin
 */

/**
 * @file plugins/generic/authorRequirements/index.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_generic_authorRequirements
 * @brief Wrapper for Author Requirements plugin.
 *
 */

require_once('AuthorRequirementsPlugin.inc.php');
return new AuthorRequirementsPlugin();
