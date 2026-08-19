<template>
    <div class="group-type-filter">
        <div class="quick-options">
            <button
                v-for="option in options"
                :key="option.value"
                :class="['quick-option', {active: value === option.value}]"
                type="button"
                @click="select(option.value)">
                {{ option.label }}<span v-if="option.pay" class="zm-group-type-pay-tag">付费</span>
            </button>
        </div>
        <a-dropdown
            v-model:open="dropdownOpen"
            :trigger="['click']"
            placement="bottomRight"
            overlayClassName="session-group-type-dropdown">
            <button
                :class="['filter-trigger', {active: dropdownOpen}]"
                type="button"
                aria-label="筛选群聊类型">
                <FilterOutlined/>
            </button>
            <template #overlay>
                <div class="group-type-menu">
                    <button
                        v-for="option in options"
                        :key="option.value"
                        :class="['menu-option', {active: value === option.value}]"
                        type="button"
                        @click="select(option.value)">
                        <span>{{ option.label }}<span v-if="option.pay" class="zm-group-type-pay-tag">付费</span></span>
                        <CheckOutlined v-if="value === option.value"/>
                    </button>
                </div>
            </template>
        </a-dropdown>
    </div>
</template>

<script setup>
import {ref} from 'vue';
import {CheckOutlined, FilterOutlined} from '@ant-design/icons-vue';

const props = defineProps({
    value: {
        type: String,
        default: '',
    },
})
const emit = defineEmits(['update:value', 'change'])
const dropdownOpen = ref(false)

const options = [
    {value: '', label: '全部'},
    {value: '1', label: '客户群'},
    {value: '2', label: '内部群', pay: true},
    {value: '3', label: '非企业客户群', pay: true},
    {value: 'unremarked_non_enterprise', label: '未备注非企业客户群', pay: true},
]

const select = value => {
    dropdownOpen.value = false
    if (value === props.value) {
        return
    }
    emit('update:value', value)
    emit('change', value)
}
</script>

<style scoped lang="less">
.group-type-filter {
    display: flex;
    align-items: center;
    gap: 6px;
    min-width: 0;
    width: 100%;

    .quick-options {
        display: flex;
        align-items: center;
        gap: 6px;
        min-width: 0;
        overflow: hidden;
    }

    button {
        border: 0;
        font: inherit;
        cursor: pointer;
    }

    .quick-option {
        flex: none;
        height: 24px;
        padding: 0 12px;
        color: #595959;
        white-space: nowrap;
        background: #f5f6f8;
        border-radius: 12px;

        &:hover,
        &.active {
            color: #2475fc;
            background: #e5efff;
        }
    }

    .filter-trigger {
        display: flex;
        flex: none;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        padding: 0;
        color: #595959;
        font-size: 16px;
        background: #eef4fc;
        border-radius: 12px;

        &:hover,
        &.active {
            color: #2475fc;
            background: #e5efff;
        }
    }
}

/* 付费标记：下拉菜单通过 teleport 渲染到 body，需用全局样式保证快捷选项与下拉菜单一致 */
:global(.zm-group-type-pay-tag) {
    display: inline-block;
    margin-left: 4px;
    padding: 1px 4px;
    border-radius: 4px;
    background: #ED744A;
    color: #ffffff;
    font-size: 12px;
    font-weight: 400;
    line-height: 16px;
    vertical-align: middle;
}

:global(.session-group-type-dropdown .ant-dropdown-menu) {
    padding: 0;
    background: transparent;
    box-shadow: none;
}

:global(.session-group-type-dropdown .group-type-menu) {
    width: 168px;
    padding: 8px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.14);
}

:global(.session-group-type-dropdown .menu-option) {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    height: 40px;
    padding: 0 16px;
    color: #595959;
    font-size: 14px;
    text-align: left;
    background: #fff;
    border: 0;
    border-radius: 8px;
    cursor: pointer;
}

:global(.session-group-type-dropdown .menu-option:hover),
:global(.session-group-type-dropdown .menu-option.active) {
    color: #2475fc;
    background: #e5efff;
}
</style>
