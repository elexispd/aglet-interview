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
        if (! Schema::hasTable('users')) {
            return;
        }

        $idColumn = DB::selectOne("SHOW COLUMNS FROM `users` LIKE 'id'");
        if (! $idColumn) {
            return;
        }

        $idType = strtolower((string) ($idColumn->Type ?? ''));
        if (str_contains($idType, 'char(36)')) {
            return;
        }

        if (! Schema::hasColumn('users', 'uuid_tmp')) {
            DB::statement('ALTER TABLE `users` ADD COLUMN `uuid_tmp` CHAR(36) NULL');
        }

        DB::statement('UPDATE `users` SET `uuid_tmp` = UUID() WHERE `uuid_tmp` IS NULL');
        DB::statement('ALTER TABLE `users` MODIFY `uuid_tmp` CHAR(36) NOT NULL');

        if (Schema::hasTable('favorites') && Schema::hasColumn('favorites', 'user_id')) {
            $favoritesUserId = DB::selectOne("SHOW COLUMNS FROM `favorites` LIKE 'user_id'");
            $favoritesUserIdType = strtolower((string) ($favoritesUserId->Type ?? ''));

            if (! str_contains($favoritesUserIdType, 'char(36)')) {
                if (! Schema::hasColumn('favorites', 'user_uuid_tmp')) {
                    DB::statement('ALTER TABLE `favorites` ADD COLUMN `user_uuid_tmp` CHAR(36) NULL');
                }

                DB::statement('UPDATE `favorites` f JOIN `users` u ON u.id = f.user_id SET f.user_uuid_tmp = u.uuid_tmp WHERE f.user_id IS NOT NULL AND f.user_uuid_tmp IS NULL');
                DB::statement('ALTER TABLE `favorites` MODIFY `user_uuid_tmp` CHAR(36) NOT NULL');
            }

            $fkExists = DB::selectOne("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'favorites' AND COLUMN_NAME = 'user_id' AND REFERENCED_TABLE_NAME = 'users' LIMIT 1");
            if ($fkExists && isset($fkExists->CONSTRAINT_NAME)) {
                DB::statement("ALTER TABLE `favorites` DROP FOREIGN KEY `{$fkExists->CONSTRAINT_NAME}`");
            }
        }

        if (Schema::hasTable('sessions') && Schema::hasColumn('sessions', 'user_id')) {
            $sessionsUserId = DB::selectOne("SHOW COLUMNS FROM `sessions` LIKE 'user_id'");
            $sessionsUserIdType = strtolower((string) ($sessionsUserId->Type ?? ''));

            if (! str_contains($sessionsUserIdType, 'char(36)')) {
                if (! Schema::hasColumn('sessions', 'user_uuid_tmp')) {
                    DB::statement('ALTER TABLE `sessions` ADD COLUMN `user_uuid_tmp` CHAR(36) NULL');
                }

                DB::statement('UPDATE `sessions` s JOIN `users` u ON u.id = s.user_id SET s.user_uuid_tmp = u.uuid_tmp WHERE s.user_id IS NOT NULL AND s.user_uuid_tmp IS NULL');

                $sessionsUserIndex = DB::selectOne("SHOW INDEX FROM `sessions` WHERE Key_name = 'sessions_user_id_index'");
                if ($sessionsUserIndex) {
                    DB::statement('ALTER TABLE `sessions` DROP INDEX `sessions_user_id_index`');
                }

                DB::statement('ALTER TABLE `sessions` DROP COLUMN `user_id`');
                DB::statement('ALTER TABLE `sessions` CHANGE `user_uuid_tmp` `user_id` CHAR(36) NULL');
                DB::statement('ALTER TABLE `sessions` ADD INDEX `sessions_user_id_index` (`user_id`)');
            }
        }

        DB::statement('ALTER TABLE `users` MODIFY `id` BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE `users` DROP PRIMARY KEY');
        DB::statement('ALTER TABLE `users` DROP COLUMN `id`');
        DB::statement('ALTER TABLE `users` CHANGE `uuid_tmp` `id` CHAR(36) NOT NULL');
        DB::statement('ALTER TABLE `users` ADD PRIMARY KEY (`id`)');

        if (Schema::hasTable('favorites') && Schema::hasColumn('favorites', 'user_id')) {
            $favoritesUserId = DB::selectOne("SHOW COLUMNS FROM `favorites` LIKE 'user_id'");
            $favoritesUserIdType = strtolower((string) ($favoritesUserId->Type ?? ''));

            if (! str_contains($favoritesUserIdType, 'char(36)') && Schema::hasColumn('favorites', 'user_uuid_tmp')) {
                $uniqueIndexExists = DB::selectOne("SHOW INDEX FROM `favorites` WHERE Key_name = 'favorites_user_tmdb_unique'");
                if ($uniqueIndexExists) {
                    DB::statement('ALTER TABLE `favorites` DROP INDEX `favorites_user_tmdb_unique`');
                }

                DB::statement('ALTER TABLE `favorites` DROP COLUMN `user_id`');
                DB::statement('ALTER TABLE `favorites` CHANGE `user_uuid_tmp` `user_id` CHAR(36) NOT NULL');

                DB::statement('ALTER TABLE `favorites` ADD UNIQUE `favorites_user_tmdb_unique` (`user_id`, `tmdb_id`)');
            }

            $uniqueIndexExists = DB::selectOne("SHOW INDEX FROM `favorites` WHERE Key_name = 'favorites_user_tmdb_unique'");
            if (! $uniqueIndexExists) {
                DB::statement('ALTER TABLE `favorites` ADD UNIQUE `favorites_user_tmdb_unique` (`user_id`, `tmdb_id`)');
            }

            $fkExists = DB::selectOne("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'favorites' AND COLUMN_NAME = 'user_id' AND REFERENCED_TABLE_NAME = 'users' LIMIT 1");
            if (! $fkExists) {
                DB::statement('ALTER TABLE `favorites` ADD CONSTRAINT `favorites_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        $idColumn = DB::selectOne("SHOW COLUMNS FROM `users` LIKE 'id'");
        if (! $idColumn) {
            return;
        }

        $idType = strtolower((string) ($idColumn->Type ?? ''));
        if (! str_contains($idType, 'char(36)')) {
            return;
        }

        if (Schema::hasTable('favorites')) {
            $fkExists = DB::selectOne("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'favorites' AND COLUMN_NAME = 'user_id' AND REFERENCED_TABLE_NAME = 'users' LIMIT 1");
            if ($fkExists && isset($fkExists->CONSTRAINT_NAME)) {
                DB::statement("ALTER TABLE `favorites` DROP FOREIGN KEY `{$fkExists->CONSTRAINT_NAME}`");
            }
        }

        DB::statement('ALTER TABLE `users` DROP PRIMARY KEY');
        DB::statement('ALTER TABLE `users` CHANGE `id` `uuid_id` CHAR(36) NOT NULL');
        DB::statement('ALTER TABLE `users` ADD COLUMN `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');

        if (Schema::hasTable('favorites') && Schema::hasColumn('favorites', 'user_id')) {
            if (! Schema::hasColumn('favorites', 'user_legacy_tmp')) {
                DB::statement('ALTER TABLE `favorites` ADD COLUMN `user_legacy_tmp` BIGINT UNSIGNED NULL');
            }

            DB::statement('UPDATE `favorites` f JOIN `users` u ON u.uuid_id = f.user_id SET f.user_legacy_tmp = u.id WHERE f.user_id IS NOT NULL AND f.user_legacy_tmp IS NULL');

            $fkExists = DB::selectOne("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'favorites' AND COLUMN_NAME = 'user_id' AND REFERENCED_TABLE_NAME = 'users' LIMIT 1");
            if ($fkExists && isset($fkExists->CONSTRAINT_NAME)) {
                DB::statement("ALTER TABLE `favorites` DROP FOREIGN KEY `{$fkExists->CONSTRAINT_NAME}`");
            }

            $uniqueIndexExists = DB::selectOne("SHOW INDEX FROM `favorites` WHERE Key_name = 'favorites_user_tmdb_unique'");
            if ($uniqueIndexExists) {
                DB::statement('ALTER TABLE `favorites` DROP INDEX `favorites_user_tmdb_unique`');
            }

            DB::statement('ALTER TABLE `favorites` DROP COLUMN `user_id`');
            DB::statement('ALTER TABLE `favorites` CHANGE `user_legacy_tmp` `user_id` BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE `favorites` ADD UNIQUE `favorites_user_tmdb_unique` (`user_id`, `tmdb_id`)');
            DB::statement('ALTER TABLE `favorites` ADD CONSTRAINT `favorites_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE');
        }

        if (Schema::hasTable('sessions') && Schema::hasColumn('sessions', 'user_id')) {
            $sessionsUserId = DB::selectOne("SHOW COLUMNS FROM `sessions` LIKE 'user_id'");
            $sessionsUserIdType = strtolower((string) ($sessionsUserId->Type ?? ''));

            if (str_contains($sessionsUserIdType, 'char(36)')) {
                if (! Schema::hasColumn('sessions', 'user_legacy_tmp')) {
                    DB::statement('ALTER TABLE `sessions` ADD COLUMN `user_legacy_tmp` BIGINT UNSIGNED NULL');
                }

                DB::statement('UPDATE `sessions` s JOIN `users` u ON u.uuid_id = s.user_id SET s.user_legacy_tmp = u.id WHERE s.user_id IS NOT NULL AND s.user_legacy_tmp IS NULL');

                $sessionsUserIndex = DB::selectOne("SHOW INDEX FROM `sessions` WHERE Key_name = 'sessions_user_id_index'");
                if ($sessionsUserIndex) {
                    DB::statement('ALTER TABLE `sessions` DROP INDEX `sessions_user_id_index`');
                }

                DB::statement('ALTER TABLE `sessions` DROP COLUMN `user_id`');
                DB::statement('ALTER TABLE `sessions` CHANGE `user_legacy_tmp` `user_id` BIGINT UNSIGNED NULL');
                DB::statement('ALTER TABLE `sessions` ADD INDEX `sessions_user_id_index` (`user_id`)');
            }
        }

        DB::statement('ALTER TABLE `users` DROP COLUMN `uuid_id`');
    }
};
