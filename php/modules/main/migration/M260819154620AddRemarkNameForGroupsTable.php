<?php

declare(strict_types=1);

use Yiisoft\Db\Migration\MigrationBuilder;
use Yiisoft\Db\Migration\RevertibleMigrationInterface;

/**
 * Class M260819154620AddRemarkNameForGroupsTable
 */
final class M260819154620AddRemarkNameForGroupsTable implements RevertibleMigrationInterface
{
    public function up(MigrationBuilder $b): void
    {
        $sql = <<<SQL
alter table main.groups add column remark_name varchar(255) not null default '';

comment on column main.groups.remark_name is '群聊备注名';
SQL;
        migrate_exec($b, $sql);
    }

    public function down(MigrationBuilder $b): void
    {
        migrate_exec($b, "alter table main.groups drop column remark_name");
    }
}
