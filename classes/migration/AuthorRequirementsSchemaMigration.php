<?php

/**
 * @file plugins/generic/authorRequirements/classes/migration/AuthorRequirementsSchemaMigration.php
 *
 * Copyright (c) 2026 Simon Fraser University
 * Copyright (c) 2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class AuthorRequirementsSchemaMigration
 *
 * @brief Allow authors to be saved without an email address.
 */

namespace APP\plugins\generic\authorRequirements\classes\migration;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PKP\install\DowngradeNotSupportedException;

class AuthorRequirementsSchemaMigration extends Migration
{
    /**
     * Make the authors' email column nullable, if it isn't already.
     *
     * The column is shared by all contexts, so it stays nullable whatever each
     * context's settings are. The plugin settings only change the validation.
     * Safe to run more than once, as the install migrations of all plugins run
     * again whenever a plugin is installed.
     */
    public function up(): void
    {
        $email = collect(Schema::getColumns('authors'))->firstWhere('name', 'email');
        if ($email['nullable']) {
            return;
        }

        Schema::table('authors', function (Blueprint $table) {
            $table->string('email', 90)->nullable()->change();
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        throw new DowngradeNotSupportedException();
    }
}
