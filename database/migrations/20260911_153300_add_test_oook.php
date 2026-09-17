<?php

/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/puff
 * https://github.com/php-puff/puff/issues
 * Copyright (c) Puff
 */

declare(strict_types=1);

use Puff\Migration\Blueprint;
use Puff\Migration\Migration;
use Puff\Migration\Schema;

return new class implements Migration {
    public function up(Schema $schema): void
    {
        // Apply add_test_oook.
        $schema->create('table_name', static function (Blueprint $table): void {
            $table->id();
            $table->string('oook');
            $table->timestamps();
        });
    }

    public function down(Schema $schema): void
    {
        // Revert add_test_oook.
        // $schema->dropIfExists('table_name');
    }
};
