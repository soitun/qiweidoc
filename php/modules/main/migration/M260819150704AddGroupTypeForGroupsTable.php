<?php

declare(strict_types=1);

use Yiisoft\Db\Migration\MigrationBuilder;
use Yiisoft\Db\Migration\RevertibleMigrationInterface;

/**
 * Class M260819150704AddGroupTypeForGroupsTable
 */
final class M260819150704AddGroupTypeForGroupsTable implements RevertibleMigrationInterface
{
    public function up(MigrationBuilder $b): void
    {
        $sql = <<<SQL
alter table main.groups add column group_type int2 not null default 1;

comment on column main.groups.group_type is '群类型: 1客户群 2内部群 3非企业客户群';
SQL;
        migrate_exec($b, $sql);
    }

    public function down(MigrationBuilder $b): void
    {
        migrate_exec($b, "alter table main.groups drop column group_type");
    }
}
