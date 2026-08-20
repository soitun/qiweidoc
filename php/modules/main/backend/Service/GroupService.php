<?php
// Copyright © 2016- 2025 Sesame Network Technology all right reserved

namespace Modules\Main\Service;

use Common\DB\BaseModel;
use Common\Yii;
use LogicException;
use Modules\Main\Enum\EnumChatConversationType;
use Modules\Main\Model\CorpModel;
use Modules\Main\Model\GroupModel;
use Throwable;
use Yiisoft\Arrays\ArrayHelper;

class GroupService
{
    /**
     * @param CorpModel $corp
     * @param $data
     * Notes: 群列表
     * User: rand
     * Date: 2024/11/14 17:02
     * @return array
     * @throws Throwable
     */
    public static function list(CorpModel $corp, $data): array
    {
        $page = $data["page"] ?? 1;
        $size = $data["size"] ?? 20;
        $groupConversationType = EnumChatConversationType::Group->value;

        $toWhere = '';
        // 搜索关键字
        if (!empty($data["keyword"])) {
            $toWhere .= " and (c.name ilike '%{$data['keyword']}%' or c.remark_name ilike '%{$data['keyword']}%' or c.chat_id = '{$data['keyword']}')";
        }

        // 群主筛选
        if (!empty($data["owner"])) {
            $toWhere .= " and c.owner = '{$data["owner"]}' ";
        }

        // 群筛选
        if (!empty($data["chat_id"])) {
            $toWhere .= " and c.chat_id = '{$data["chat_id"]}' ";
        }

        // 群创建时间
        if (!empty($data["start_time"]) && !empty($data["stop_time"])) {
            $toWhere .= " and c.created_at between '{$data['start_time']}' and '{$data['stop_time']}' ";
        }

        // 会话场景：查询全部群聊类型（含内部群、非企业客户群）
        if (!empty($data['has_conversation'])) {
            $toWhere .= " and exists (
                select 1
                from main.chat_conversations as conversation
                where conversation.corp_id = c.corp_id
                  and conversation.\"to\" = c.chat_id
                  and conversation.type = {$groupConversationType}
            ) ";
        } else {
            // 单纯查询客户群列表：只查询客户群
            $toWhere .= " and c.group_type = " . GroupModel::GROUP_TYPE_CUSTOMER . " ";
        }

        // 群类型筛选
        if (in_array($data['group_type'] ?? '', ['1', '2', '3'], true)) {
            $toWhere .= " and c.group_type = {$data['group_type']} ";
        } elseif (($data['group_type'] ?? '') === 'unremarked_non_enterprise') {
            $toWhere .= " and c.group_type = 3 and coalesce(c.remark_name, '') = '' ";
        }

        // 排序条件
        $order_by = "group_create_time";
        if (isset($data["order_by"])) {
            $order_by = $data["order_by"];
        }


        // 拼接基础sql
        $offset = ($page - 1) * $size;
        $baseSql = /** @lang sql */ <<<SQL
select v.id as conversations_id, v.last_msg_time, v.type,v.is_collect,v.collect_reason,v.collect_time, c.*
from main.groups as c
left join main.chat_conversations as v on c.chat_id= v."to" and v.corp_id = '{$corp->get('id')}'  and v.type = {$groupConversationType}
where c.corp_id = '{$corp->get('id')}' {$toWhere} order by {$order_by} desc
SQL;
        $countSql = "select count(*) as total from({$baseSql})";
        $listSql = "{$baseSql} offset {$offset} limit {$size}";

        $totalRes = Yii::db()->createCommand($countSql)->queryColumn()[0];
        $listRes = Yii::db()->createCommand($listSql)->queryAll();

        $res = BaseModel::buildPaginate($page, $size, $totalRes, $listRes);



        if (!empty($res['items'])) {
            $staffListIndex = [];
            foreach ($res["items"] as &$group) {
                $group['admin_list'] = json_decode($group['admin_list'], true);
                $group['member_list'] = json_decode($group['member_list'], true);
                $staffList = array_filter($group['member_list'], function ($node) {
                    return $node["type"] == 1;
                });
                unset($group['member_list']);
                $staffListIndex = array_merge($staffListIndex, ArrayHelper::index($staffList, "userid"));
            }
            unset($group);

            // 群主名称
            foreach ($res['items'] as &$group) {
                $staff = $staffListIndex[$group['owner']] ?? [];
                $group['owner_name'] = $staff['name'] ?? '';
            }
            unset($group);
        }

        // 上次同步时间
        $res['last_sync_time'] = date("Y-m-d H:i:s", strtotime($corp->get('sync_group_time')));


        return $res;
    }

    /**
     * 保存群聊备注名
     *
     * @param CorpModel $corp
     * @param string $chatId
     * @param string $remarkName
     * @return void
     * @throws Throwable
     */
    public static function saveRemarkName(CorpModel $corp, string $chatId, string $remarkName): void
    {
        if (empty($chatId)) {
            throw new LogicException("群聊id不能为空");
        }

        $group = GroupModel::query()
            ->where(['and',
                ['corp_id' => $corp->get('id')],
                ['chat_id' => $chatId],
            ])
            ->getOne();

        if (empty($group)) {
            throw new LogicException("群聊不存在");
        }

        $group->update(['remark_name' => $remarkName]);
    }
}
