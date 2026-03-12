<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('favorites')) {
            return;
        }

        $idColumn = DB::selectOne("SHOW COLUMNS FROM `favorites` LIKE 'id'");
        if (! $idColumn) {
            return;
        }

        $idType = strtolower((string) ($idColumn->Type ?? ''));
        if (str_contains($idType, 'char(36)')) {
            return;
        }

        $uuidTmpExists = Schema::hasColumn('favorites', 'uuid_tmp');
        if (! $uuidTmpExists) {
            DB::statement('ALTER TABLE `favorites` ADD COLUMN `uuid_tmp` CHAR(36) NULL');
        }

        DB::statement('UPDATE `favorites` SET `uuid_tmp` = UUID() WHERE `uuid_tmp` IS NULL');
        DB::statement('ALTER TABLE `favorites` MODIFY `uuid_tmp` CHAR(36) NOT NULL');

        DB::statement('ALTER TABLE `favorites` MODIFY `id` BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE `favorites` DROP PRIMARY KEY');
        DB::statement('ALTER TABLE `favorites` DROP COLUMN `id`');
        DB::statement('ALTER TABLE `favorites` CHANGE `uuid_tmp` `id` CHAR(36) NOT NULL');
        DB::statement('ALTER TABLE `favorites` ADD PRIMARY KEY (`id`)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('favorites')) {
            return;
        }

        $idColumn = DB::selectOne("SHOW COLUMNS FROM `favorites` LIKE 'id'");
        if (! $idColumn) {
            return;
        }

        $idType = strtolower((string) ($idColumn->Type ?? ''));
        if (! str_contains($idType, 'char(36)')) {
            return;
        }

        DB::statement('ALTER TABLE `favorites` DROP PRIMARY KEY');
        DB::statement('ALTER TABLE `favorites` ADD COLUMN `legacy_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');
        DB::statement('ALTER TABLE `favorites` DROP COLUMN `id`');
        DB::statement('ALTER TABLE `favorites` CHANGE `legacy_id` `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
    }
};
