<?php

declare(strict_types=1);

use Yiisoft\Db\Migration\MigrationBuilder;
use Yiisoft\Db\Migration\RevertibleMigrationInterface;

/**
 * 为按企业和群 ID 查询群会话增加部分索引。
 */
final class M260820120000AddGroupConversationLookupIndex implements RevertibleMigrationInterface
{
    public function up(MigrationBuilder $b): void
    {
        $sql = /** @lang sql */ <<<SQL
create index idx_chat_conversations_group_lookup
    on main.chat_conversations (corp_id, "to")
    where type = 2
SQL;

        migrate_exec($b, $sql);
    }

    public function down(MigrationBuilder $b): void
    {
        migrate_exec($b, 'drop index main.idx_chat_conversations_group_lookup');
    }
}
