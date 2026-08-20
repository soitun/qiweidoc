<template>
    <div class="group-type-filter">
        <div ref="quickOptionsRef" class="quick-options">
            <button
                v-for="option in options"
                :key="option.value"
                :ref="element => setOptionRef(option.value, element)"
                :class="['quick-option', {active: value === option.value}]"
                type="button"
                :aria-pressed="value === option.value"
                @click="select(option.value)">
                <span class="option-label">
                    {{ option.label }}
                    <span
                        v-if="option.pay && showPaymentTag"
                        class="zm-payment-tag"
                        @click.stop="paymentModalShow"></span>
                </span>
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
                        <span class="option-label">
                            {{ option.label }}
                            <span
                                v-if="option.pay && showPaymentTag"
                                class="zm-payment-tag"
                                @click.stop="paymentModalShow"></span>
                        </span>
                        <CheckOutlined v-if="value === option.value"/>
                    </button>
                </div>
            </template>
        </a-dropdown>
    </div>
</template>

<script setup>
import {computed, nextTick, ref, watch} from 'vue';
import {useRouter} from 'vue-router';
import {useStore} from 'vuex';
import {Modal} from 'ant-design-vue';
import {CheckOutlined, FilterOutlined} from '@ant-design/icons-vue';
import {getPluginRouteParams} from "@/utils/tools";

const props = defineProps({
    value: {
        type: String,
        default: '',
    },
})
const emit = defineEmits(['update:value', 'change'])
const dropdownOpen = ref(false)
const quickOptionsRef = ref(null)
const optionRefs = new Map()
const router = useRouter()
const store = useStore()

const archiveStfModule = computed(() => store.getters.getArchiveStfInfo || {})
const showPaymentTag = computed(() => {
    const module = archiveStfModule.value
    return !module.is_install || module.is_expired || module.has_bought != 1
})

const options = [
    {value: '', label: '全部'},
    {value: '1', label: '客户群'},
    {value: '2', label: '内部群', pay: true},
    {value: '3', label: '非企业客户群', pay: true},
    {value: 'unremarked_non_enterprise', label: '未命名群聊', pay: true},
]

const setOptionRef = (value, element) => {
    if (element) {
        optionRefs.set(value, element)
    } else {
        optionRefs.delete(value)
    }
}

// 让选中项尽量位于可视区域中间，以便同时看到它左右相邻的筛选项。
const scrollToSelected = (value, behavior = 'smooth') => {
    const container = quickOptionsRef.value
    const selected = optionRefs.get(value)
    if (!container || !selected) {
        return
    }

    const containerRect = container.getBoundingClientRect()
    const selectedRect = selected.getBoundingClientRect()
    const selectedCenterOffset = selectedRect.left - containerRect.left
        + selectedRect.width / 2
    const maxScrollLeft = container.scrollWidth - container.clientWidth
    const scrollLeft = container.scrollLeft + selectedCenterOffset
        - container.clientWidth / 2

    container.scrollTo({
        left: Math.max(0, Math.min(scrollLeft, maxScrollLeft)),
        behavior,
    })
}

watch(
    () => props.value,
    (value, oldValue) => nextTick(() => {
        scrollToSelected(value, oldValue === undefined ? 'auto' : 'smooth')
    }),
    {immediate: true}
)

const paymentModalShow = () => {
    Modal.confirm({
        title: '查看非企业客户群/内部群消息需购买开启后使用',
        content: '确认去购买吗？',
        okText: '去购买',
        onOk: linkPlugHome,
    })
}

const checkPaidGroupPermission = async () => {
    if (showPaymentTag.value) {
        paymentModalShow()
        return false
    }

    if (!archiveStfModule.value.is_enabled) {
        Modal.confirm({
            title: '查看非企业客户群/内部群消息需启用插件',
            content: '请先启用「存档消息管理」插件',
            okText: '去启用',
            onOk: () => router.push('/plug/index')
        })
        return false
    }

    const settings = await store.dispatch('updateArchiveStfSetting')
    if (settings.enable_view_non_customer_group != 1) {
        Modal.confirm({
            title: '提示',
            content: '查看非企业客户群/内部群消息已禁用，如需使用请开启「存档消息管理」插件中的“查看非企业客户群/内部群消息”开关',
            okText: '去设置',
            onOk: () => router.push(getPluginRouteParams({name: 'archive_staff'}))
        })
        return false
    }
    return true
}

const linkPlugHome = () => {
    const link = router.resolve({
        path: '/plug/index',
    })
    window.open(link.href)
}

const select = async value => {
    dropdownOpen.value = false
    if (value === props.value) {
        return
    }
    const option = options.find(item => item.value === value)
    if (option?.pay && !await checkPaidGroupPermission()) {
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
        flex: 1 1 auto;
        align-items: center;
        gap: 6px;
        min-width: 0;
        overflow-x: auto;
        overflow-y: hidden;
        overscroll-behavior-x: contain;
        scrollbar-width: none;
        touch-action: pan-x;

        &::-webkit-scrollbar {
            display: none;
        }
    }

    button {
        border: 0;
        font: inherit;
        cursor: pointer;
    }

    .option-label {
        display: flex;
        align-items: center;
        white-space: nowrap;
    }

    .quick-option {
        flex: none;
        height: 24px;
        padding: 0 12px;
        color: #595959;
        white-space: nowrap;
        background: #f5f6f8;
        border-radius: 12px;

        &:not(.active):hover {
            background: #f2f4f7;
        }

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

:global(.session-group-type-dropdown .ant-dropdown-menu) {
    padding: 0;
    background: transparent;
    box-shadow: none;
}

:global(.session-group-type-dropdown .group-type-menu) {
    width: 200px;
    padding: 8px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.14);
}

:global(.session-group-type-dropdown .option-label) {
    display: flex;
    align-items: center;
    white-space: nowrap;
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

:global(.session-group-type-dropdown .menu-option:not(.active):hover) {
    background: #f2f4f7;
}

:global(.session-group-type-dropdown .menu-option.active) {
    color: #2475fc;
    background: #e5efff;
}
</style>
