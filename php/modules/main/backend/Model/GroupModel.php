<?php
// Copyright © 2016- 2025 Sesame Network Technology all right reserved

namespace Modules\Main\Model;

use Common\DB\BaseModel;
use Exception;
use LogicException;
use Throwable;

class GroupModel extends BaseModel
{
    // 群类型：客户群
    public const GROUP_TYPE_CUSTOMER = 1;
    // 群类型：内部群
    public const GROUP_TYPE_INTERNAL = 2;
    // 群类型：非企业客户群
    public const GROUP_TYPE_NON_ENTERPRISE = 3;

    public function getTableName(): string
    {
        return "main.groups";
    }

    protected function casts(): array
    {
        return [
            "id" => 'int',
            "created_at" => 'string',
            "updated_at" => 'string',
            "corp_id" => 'string',
            "chat_id" => 'string',
            "name" => 'string',
            "remark_name" => 'string',
            "owner" => 'string',
            "member_version" => 'string',
            "group_status" => 'int',
            "group_type" => 'int',
            "group_create_time" => 'string',
            "staff_num" => 'int',
            "cst_num" => 'int',
            "total_member" => 'int',
            "member_list" => 'array',
            "admin_list" => 'array',
            "has_conversation" => 'boolean',
        ];
    }

    /**
     * @throws Throwable
     */
    public static function hasConversationSave(CorpModel $corp, string $chatId): void
    {
        $group = self::query()
            ->where(['and',
                ['corp_id' => $corp->get('id')],
                ['chat_id' => $chatId],
            ])
            ->getOne();
        if (!empty($group)) {
            $group->update(['has_conversation' => true]);
        }
    }

    /**
     * 从企微同步单条数据
     *
     * @throws Throwable
     */
    public static function syncOne(CorpModel $corp, string $chatID): void
    {
        $res = $corp->postWechatApi('/cgi-bin/externalcontact/groupchat/get', ['chat_id' => $chatID], 'json');
        if (empty($res['group_chat'])) {
            throw new LogicException('请求企微获取客户群详情数据失败');
        }

        $staffUserNum = count(array_filter($res['group_chat']["member_list"], function ($item) {
            return $item["type"] == 1;
        }));
        $cstUserNum = count(array_filter($res['group_chat']["member_list"], function ($item) {
            return $item["type"] == 2;
        }));
        GroupModel::updateOrCreate(['and',
            ["corp_id" => $corp->get('id')],
            ["chat_id" => $chatID],
        ], [
            "corp_id" => $corp->get('id'),
            "chat_id" => $chatID,
            "name" => $res['group_chat']["name"] ?? "",
            "group_create_time" => date('Y-m-d H:i:s', $res['group_chat']["create_time"] ?? 0),
            "member_list" => $res['group_chat']["member_list"] ?? [],
            "owner" => $res['group_chat']["owner"] ?? "",
            "group_status" => $res['group_chat']["chat_id"] ?? 0,
            "member_version" => $res['group_chat']["member_version"] ?? "",
            "admin_list" => $res['group_chat']["admin_list"] ?? [],
            "staff_num" => $staffUserNum,
            "cst_num" => $cstUserNum,
            "total_member" => $staffUserNum + $cstUserNum,
            "group_type" => self::GROUP_TYPE_CUSTOMER,
        ]);
    }

    /**
     * 群聊消息拉取时，确保群信息存在（不存在时查询企微内部群接口补全）
     *
     * @throws Throwable
     */
    public static function ensureGroupExists(CorpModel $corp, string $chatId): void
    {
        $group = self::query()
            ->where(['and',
                ['corp_id' => $corp->get('id')],
                ['chat_id' => $chatId],
            ])
            ->getOne();
        if (!empty($group)) {
            return;
        }

        $isInternal = false;
        $groupName = '';
        $owner = '';
        $createTime = 0;
        $members = [];
        try {
            $res = $corp->postWechatApi('/cgi-bin/msgaudit/groupchat/get', ['roomid' => $chatId], 'json', CorpModel::SecretTypeChat);
            $isInternal = true;
            $groupName = $res['roomname'] ?? '';
            $owner = $res['creator'] ?? '';
            $createTime = $res['room_create_time'] ?? 0;
            $members = $res['members'] ?? [];
        } catch (Throwable) {
            // 非内部群或接口调用失败，按非企业客户群处理
        }

        if ($isInternal) {
            // 内部群接口的 members 为 [{memberid, jointime}]，转换为 member_list 结构（内部群成员均为企业员工 type=1）
            $memberList = [];
            foreach ($members as $member) {
                $memberList[] = [
                    'type' => 1,
                    'userid' => $member['memberid'] ?? '',
                    'join_time' => $member['jointime'] ?? 0,
                ];
            }

            self::updateOrCreate(['and',
                ['corp_id' => $corp->get('id')],
                ['chat_id' => $chatId],
            ], [
                'corp_id' => $corp->get('id'),
                'chat_id' => $chatId,
                'name' => $groupName,
                'owner' => $owner,
                'group_create_time' => date('Y-m-d H:i:s', $createTime),
                'member_list' => $memberList,
                'staff_num' => count($memberList),
                'cst_num' => 0,
                'total_member' => count($memberList),
                'group_type' => self::GROUP_TYPE_INTERNAL,
            ]);
        } else {
            self::updateOrCreate(['and',
                ['corp_id' => $corp->get('id')],
                ['chat_id' => $chatId],
            ], [
                'corp_id' => $corp->get('id'),
                'chat_id' => $chatId,
                'name' => '非企业客户群',
                'group_type' => self::GROUP_TYPE_NON_ENTERPRISE,
            ]);
        }
    }
}
